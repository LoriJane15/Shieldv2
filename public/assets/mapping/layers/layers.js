var wms_layers = [];


        var lyr_GoogleSatellite_0 = new ol.layer.Tile({
            'title': 'Google Satellite',
            'type':'base',
            'opacity': 1.000000,
            
            
            source: new ol.source.XYZ({
            attributions: ' ',
                url: 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}'
            })
        });
var format_BarangayBansalan_1 = new ol.format.GeoJSON();
var features_BarangayBansalan_1 = format_BarangayBansalan_1.readFeatures(json_BarangayBansalan_1, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayBansalan_1 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayBansalan_1.addFeatures(features_BarangayBansalan_1);
var lyr_BarangayBansalan_1 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayBansalan_1, 
                style: style_BarangayBansalan_1,
                popuplayertitle: "Barangay ( Bansalan )",
                interactive: true,
                title: '<img src="../styles/legend/digos logo.jpg" /> Barangay ( Bansalan )'
            });
var format_BarangayMagsaysay_2 = new ol.format.GeoJSON();
var features_BarangayMagsaysay_2 = format_BarangayMagsaysay_2.readFeatures(json_BarangayMagsaysay_2, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayMagsaysay_2 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayMagsaysay_2.addFeatures(features_BarangayMagsaysay_2);
var lyr_BarangayMagsaysay_2 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayMagsaysay_2, 
                style: style_BarangayMagsaysay_2,
                popuplayertitle: "Barangay ( Magsaysay )",
                interactive: true,
                title: '<img src="styles/legend/BarangayMagsaysay_2.png" /> Barangay ( Magsaysay )'
            });
var format_BarangayMatanao_3 = new ol.format.GeoJSON();
var features_BarangayMatanao_3 = format_BarangayMatanao_3.readFeatures(json_BarangayMatanao_3, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayMatanao_3 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayMatanao_3.addFeatures(features_BarangayMatanao_3);
var lyr_BarangayMatanao_3 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayMatanao_3, 
                style: style_BarangayMatanao_3,
                popuplayertitle: "Barangay ( Matanao )",
                interactive: true,
                title: '<img src="styles/legend/BarangayMatanao_3.png" /> Barangay ( Matanao )'
            });
var format_BarangayKiblawan_4 = new ol.format.GeoJSON();
var features_BarangayKiblawan_4 = format_BarangayKiblawan_4.readFeatures(json_BarangayKiblawan_4, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayKiblawan_4 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayKiblawan_4.addFeatures(features_BarangayKiblawan_4);
var lyr_BarangayKiblawan_4 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayKiblawan_4, 
                style: style_BarangayKiblawan_4,
                popuplayertitle: "Barangay ( Kiblawan )",
                interactive: true,
                title: '<img src="styles/legend/BarangayKiblawan_4.png" /> Barangay ( Kiblawan )'
            });
var format_BarangaySulop_5 = new ol.format.GeoJSON();
var features_BarangaySulop_5 = format_BarangaySulop_5.readFeatures(json_BarangaySulop_5, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangaySulop_5 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangaySulop_5.addFeatures(features_BarangaySulop_5);
var lyr_BarangaySulop_5 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangaySulop_5, 
                style: style_BarangaySulop_5,
                popuplayertitle: "Barangay ( Sulop )",
                interactive: true,
                title: '<img src="styles/legend/BarangaySulop_5.png" /> Barangay ( Sulop )'
            });
var format_BarangayMalalag_6 = new ol.format.GeoJSON();
var features_BarangayMalalag_6 = format_BarangayMalalag_6.readFeatures(json_BarangayMalalag_6, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayMalalag_6 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayMalalag_6.addFeatures(features_BarangayMalalag_6);
var lyr_BarangayMalalag_6 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayMalalag_6, 
                style: style_BarangayMalalag_6,
                popuplayertitle: "Barangay ( Malalag )",
                interactive: true,
                title: '<img src="styles/legend/BarangayMalalag_6.png" /> Barangay ( Malalag )'
            });
var format_BarangayPadada_7 = new ol.format.GeoJSON();
var features_BarangayPadada_7 = format_BarangayPadada_7.readFeatures(json_BarangayPadada_7, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayPadada_7 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayPadada_7.addFeatures(features_BarangayPadada_7);
var lyr_BarangayPadada_7 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayPadada_7, 
                style: style_BarangayPadada_7,
                popuplayertitle: "Barangay ( Padada )",
                interactive: true,
                title: '<img src="styles/legend/BarangayPadada_7.png" /> Barangay ( Padada )'
            });
var format_BarangayHagonoy_8 = new ol.format.GeoJSON();
var features_BarangayHagonoy_8 = format_BarangayHagonoy_8.readFeatures(json_BarangayHagonoy_8, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayHagonoy_8 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayHagonoy_8.addFeatures(features_BarangayHagonoy_8);
var lyr_BarangayHagonoy_8 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayHagonoy_8, 
                style: style_BarangayHagonoy_8,
                popuplayertitle: "Barangay ( Hagonoy )",
                interactive: true,
                title: '<img src="styles/legend/BarangayHagonoy_8.png" /> Barangay ( Hagonoy )'
            });
var format_BarangayDigos_9 = new ol.format.GeoJSON();
var features_BarangayDigos_9 = format_BarangayDigos_9.readFeatures(json_BarangayDigos_9, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayDigos_9 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayDigos_9.addFeatures(features_BarangayDigos_9);
var lyr_BarangayDigos_9 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayDigos_9, 
                style: style_BarangayDigos_9,
                popuplayertitle: "Barangay ( Digos )",
                interactive: true,
                title: '<img src="styles/legend/BarangayDigos_9.png" /> Barangay ( Digos )'
            });
var format_BarangayStacruz_10 = new ol.format.GeoJSON();
var features_BarangayStacruz_10 = format_BarangayStacruz_10.readFeatures(json_BarangayStacruz_10, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_BarangayStacruz_10 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_BarangayStacruz_10.addFeatures(features_BarangayStacruz_10);
var lyr_BarangayStacruz_10 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_BarangayStacruz_10, 
                style: style_BarangayStacruz_10,
                popuplayertitle: "Barangay ( Sta cruz )",
                interactive: true,
                title: '<img src="styles/legend/BarangayStacruz_10.png" /> Barangay ( Sta cruz )'
            });
var format_10Municipalities_11 = new ol.format.GeoJSON();
var features_10Municipalities_11 = format_10Municipalities_11.readFeatures(json_10Municipalities_11, 
            {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_10Municipalities_11 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_10Municipalities_11.addFeatures(features_10Municipalities_11);
var lyr_10Municipalities_11 = new ol.layer.Vector({
                declutter: false,
                source:jsonSource_10Municipalities_11, 
                style: style_10Municipalities_11,
                popuplayertitle: "10 Municipalities",
                interactive: true,
                title: '<img src="styles/legend/10Municipalities_11.png" /> 10 Municipalities'
            });

lyr_GoogleSatellite_0.setVisible(true);lyr_BarangayBansalan_1.setVisible(true);lyr_BarangayMagsaysay_2.setVisible(true);lyr_BarangayMatanao_3.setVisible(true);lyr_BarangayKiblawan_4.setVisible(true);lyr_BarangaySulop_5.setVisible(true);lyr_BarangayMalalag_6.setVisible(true);lyr_BarangayPadada_7.setVisible(true);lyr_BarangayHagonoy_8.setVisible(true);lyr_BarangayDigos_9.setVisible(true);lyr_BarangayStacruz_10.setVisible(true);lyr_10Municipalities_11.setVisible(false);
var layersList = [lyr_GoogleSatellite_0,lyr_BarangayBansalan_1,lyr_BarangayMagsaysay_2,lyr_BarangayMatanao_3,lyr_BarangayKiblawan_4,lyr_BarangaySulop_5,lyr_BarangayMalalag_6,lyr_BarangayPadada_7,lyr_BarangayHagonoy_8,lyr_BarangayDigos_9,lyr_BarangayStacruz_10,lyr_10Municipalities_11];
lyr_BarangayBansalan_1.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangayMagsaysay_2.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangayMatanao_3.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangayKiblawan_4.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangaySulop_5.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangayMalalag_6.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangayPadada_7.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangayHagonoy_8.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangayDigos_9.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_BarangayStacruz_10.set('fieldAliases', {'fid': 'fid', 'GID_0': 'GID_0', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'NAME_3': 'Barangay', 'TYPE_3': 'TYPE_3', 'ENGTYPE_3': 'ENGTYPE_3', 'Fr\'s': 'Fr\'s', 'Status': 'Status', });
lyr_10Municipalities_11.set('fieldAliases', {'fid': 'fid', 'COUNTRY': 'COUNTRY', 'NAME_1': 'Province', 'NAME_2': 'Municipality', 'Total of Fr\'s': 'Total of Fr\'s', 'Status': 'Status', });
lyr_BarangayBansalan_1.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangayMagsaysay_2.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangayMatanao_3.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangayKiblawan_4.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangaySulop_5.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangayMalalag_6.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangayPadada_7.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangayHagonoy_8.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangayDigos_9.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_BarangayStacruz_10.set('fieldImages', {'fid': 'TextEdit', 'GID_0': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'NAME_3': 'TextEdit', 'TYPE_3': 'TextEdit', 'ENGTYPE_3': 'TextEdit', 'Fr\'s': '', 'Status': '', });
lyr_10Municipalities_11.set('fieldImages', {'fid': 'TextEdit', 'COUNTRY': 'TextEdit', 'NAME_1': 'TextEdit', 'NAME_2': 'TextEdit', 'Total of Fr\'s': '', 'Status': '', });
lyr_BarangayBansalan_1.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangayMagsaysay_2.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangayMatanao_3.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'header label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangayKiblawan_4.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangaySulop_5.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangayMalalag_6.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangayPadada_7.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangayHagonoy_8.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangayDigos_9.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_BarangayStacruz_10.set('fieldLabels', {'fid': 'hidden field', 'GID_0': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'NAME_3': 'inline label - always visible', 'TYPE_3': 'hidden field', 'ENGTYPE_3': 'hidden field', 'Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_10Municipalities_11.set('fieldLabels', {'fid': 'hidden field', 'COUNTRY': 'hidden field', 'NAME_1': 'inline label - always visible', 'NAME_2': 'inline label - always visible', 'Total of Fr\'s': 'inline label - always visible', 'Status': 'inline label - always visible', });
lyr_10Municipalities_11.on('precompose', function(evt) {
    evt.context.globalCompositeOperation = 'normal';
});

lyr_BarangayBansalan_1.on('click', function(evt) {
    map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
        if (layer === lyr_BarangayBansalan_1) {
            var properties = feature.getProperties();
            console.log('Clicked properties:', properties); // Debug log
            showSidebar('BarangayBansalan_1', properties);
        }
    });
});
lyr_BarangayMagsaysay_2.on('click', function(evt) {
    map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
        if (layer === lyr_BarangayMagsaysay_2) {
            var properties = feature.getProperties();
            console.log('Clicked properties:', properties); // Debug log
            showSidebar('BarangayMagsaysay_2', properties);
        }
    });
});
lyr_BarangayMatanao_3.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('BarangayMatanao_3', properties);
    }
});
lyr_BarangayKiblawan_4.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('BarangayKiblawan_4', properties);
    }
});
lyr_BarangaySulop_5.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('BarangaySulop_5', properties);
    }
});
lyr_BarangayMalalag_6.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('BarangayMalalag_6', properties);
    }
});
lyr_BarangayPadada_7.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('BarangayPadada_7', properties);
    }
});
lyr_BarangayHagonoy_8.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('BarangayHagonoy_8', properties);
    }
});
lyr_BarangayDigos_9.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('BarangayDigos_9', properties);
    }
});
lyr_BarangayStacruz_10.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('BarangayStacruz_10', properties);
    }
});
lyr_10Municipalities_11.on('click', function(evt) {
    var feature = evt.target.getFeatures()[0];
    if (feature) {
        var properties = feature.getProperties();
        showSidebar('10Municipalities_11', properties);
    }
});

// For each layer (replace layerName with your actual layer variable)
lyr_BarangayBansalan_1.on('singleclick', function(evt) {
    map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
        if (layer === lyr_BarangayBansalan_1) {
            const properties = feature.getProperties();
            showSidebar('BarangayBansalan', properties);
        }
    });
});

// For each of your layers
addClickHandler(BarangayBansalan_1);
addClickHandler(BarangayMagsaysay_2);
// ... add for all your layers

var layers = [
    lyr_BarangayBansalan_1,
    lyr_BarangayMagsaysay_2
    // ... add all your layers here
];

layers.forEach(function(layer) {
    layer.on('singleclick', function(evt) {
        map.forEachFeatureAtPixel(evt.pixel, function(feature, clickedLayer) {
            if (clickedLayer === layer) {
                const properties = feature.getProperties();
                const municipality = properties.NAME_2;
                const barangay = properties.NAME_3;
                console.log(`Clicked: ${barangay} in ${municipality}`);
                showSidebar(feature);
            }
        });
    });
});