class OprEventTranslator
  def self.translate(opr_event_model)
    view_model = OprEventViewModel.new
    view_model.id = opr_event_model.id
    view_model.asset_key = opr_event_model.asset_key
    view_model.mst_item_id = opr_event_model.mst_item_id
    view_model.start_at = opr_event_model.start_at
    view_model.end_at = opr_event_model.end_at
    view_model.display_at = opr_event_model.display_at
    view_model.sort_number = opr_event_model.sort_number
    view_model
  end
end
