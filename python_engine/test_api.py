from core.api import make_request
import json

data = make_request(
    "match/list",
    {
        "date": "2026-05-14",
        "sport_slug": "football"
    }
)

print(json.dumps(data, indent=2, ensure_ascii=False))