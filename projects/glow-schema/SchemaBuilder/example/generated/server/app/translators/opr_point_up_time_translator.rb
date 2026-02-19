class OprPointUpTimeTranslator
  def self.translate(opr_point_up_time_model)
    view_model = OprPointUpTimeViewModel.new
    view_model.opr_event_id = opr_point_up_time_model.opr_event_id
    view_model.percentage = opr_point_up_time_model.percentage
    view_model.start_time = opr_point_up_time_model.start_time
    view_model.end_time = opr_point_up_time_model.end_time
    view_model
  end
end
