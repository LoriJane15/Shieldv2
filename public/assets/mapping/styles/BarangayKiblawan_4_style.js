var size = 0;
var placement = "point";

var style_BarangayKiblawan_4 = function (feature, resolution) {
  var context = {
    feature: feature,
    variables: {},
  };
  var value = "";
  var labelText = "";
  size = 0;
  var labelFont = "13.0px 'Open Sans', sans-serif";
  var labelFill = "#323232";
  var bufferColor = "";
  var bufferWidth = 0;
  var textAlign = "left";
  var offsetX = 8;
  var offsetY = 3;
  var placement = "point";
  if (feature.get("NAME_3") !== null) {
    labelText = String(feature.get("NAME_3"));
  }
  var style = [
    new ol.style.Style({
      stroke: new ol.style.Stroke({
        color: "rgba(35,35,35,1.0)",
        lineDash: null,
        lineCap: "butt",
        lineJoin: "miter",
        width: 0.988,
      }),
      fill: new ol.style.Fill({ color: "rgba(190,178,151,0.1)" }),
      text: createTextStyle(
        feature,
        resolution,
        labelText,
        labelFont,
        labelFill,
        placement,
        bufferColor,
        bufferWidth
      ),
    }),
  ];

  return style;
};
