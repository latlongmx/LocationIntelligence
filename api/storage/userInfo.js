// mapserver template
[resultset layer="usermap" nodata=""]
{
  "type": "FeatureCollection",
  "features": [
    [feature trimlast=","]
    {
      "type": "Feature",
      "id": "[item name="gid" format="$value" escape=none]",
      "geometry": {
        "type": "PointLineString",
        "coordinates": [
          {
            "type": "Point",
            "coordinates": [[x], [y]]
          }
        ]
      },
      "properties": {
        "description": "[item name="data_values" format="$value" escape=none]"
      }
    },
    [/feature]
  ]
}
[/resultset]
