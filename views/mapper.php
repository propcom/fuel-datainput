<h1>Map Your Data</h1>
<ul id="import-buckets">
</ul>
<div id="column-bin">
<h3>Remove Mapping:</h3>
<small>Drag a mapped column here to remove its association with a model field.</small>
</div>
<div class="clear"></div>
<div class="column-cont">
<h4>CSV Columns:</h4>
<? foreach($columns as $column): ?>
<div class="drag-box csv-column" rel="<?=$column?>"><?=ucfirst(strtolower($column))?></div>
<? endforeach; ?>
</div>
<div class="column-cont">
<h4>Generic Data:</h4>
<div class="drag-box csv-column" rel="TRUE">True</div>
<div class="drag-box csv-column" rel="FALSE">False</div>
<div class="drag-box csv-column" rel="INCREMENTALID">Incremental ID</div>
<div class="drag-box csv-column" rel="TIMESTAMP">Timestamp</div>
</div>
<?=$fieldset->build()?>