"""Fit a straight line to three calendar months and predict the following month."""

import json
import math
import re
import sys
from datetime import date
from decimal import ROUND_HALF_UP, Decimal, localcontext

from sklearn.linear_model import LinearRegression


def month(value):
    if not isinstance(value, str) or not re.fullmatch(r"[1-9][0-9]{3}-(0[1-9]|1[0-2])", value):
        raise ValueError("Expected YYYY-MM")
    year, month_number = (int(part) for part in value.split("-"))
    return date(year, month_number, 1)


def month_index(value):
    parsed = month(value)
    return parsed.year * 12 + parsed.month


def fit(values, target):
    target_index = month_index(target)
    if not isinstance(values, list) or len(values) != 3:
        raise ValueError("Expected three preceding months")
    if [month_index(row["month"]) for row in values] != list(range(target_index - 3, target_index)):
        raise ValueError("Expected consecutive months ending before the target")
    amounts = [row["amount"] for row in values]
    if any(not isinstance(value, str) or not re.fullmatch(r"0|[1-9][0-9]{0,29}", value) for value in amounts):
        raise ValueError("Expected nonnegative integer IDR strings")

    model = LinearRegression().fit([[0], [1], [2]], [int(value) for value in amounts])
    prediction = float(model.predict([[3]])[0])
    if not math.isfinite(prediction):
        raise ValueError("Prediction must be finite")
    with localcontext() as context:
        context.prec = 64
        decimal_value = Decimal(str(prediction))
        rounded = decimal_value.quantize(Decimal("1"), rounding=ROUND_HALF_UP)
        return {
            "amount": str(int(rounded)),
            "raw": format(decimal_value, "f"),
            "negative": prediction < 0,
        }


def run(data):
    target = data["target_month"]
    prediction = fit(data["actual"], target)
    holdout = data.get("holdout")
    evaluation = None
    if holdout is not None:
        evaluation_month = holdout["target_month"]
        if month_index(evaluation_month) != month_index(target) - 1:
            raise ValueError("Expected holdout directly before the forecast target")
        evaluation = {"month": evaluation_month, **fit(holdout["actual"], evaluation_month)}
    return {
        "target_month": target,
        "model": "LinearRegression",
        "prediction": prediction,
        "holdout": evaluation,
    }


if __name__ == "__main__":
    try:
        print(json.dumps(run(json.load(sys.stdin)), allow_nan=False))
    except (KeyError, TypeError, ValueError, ArithmeticError) as error:
        print(f"Forecast input or model error: {error}", file=sys.stderr)
        sys.exit(1)
