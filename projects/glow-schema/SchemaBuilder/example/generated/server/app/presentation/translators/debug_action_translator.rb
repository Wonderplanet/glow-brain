class DebugActionTranslator
  def self.translate(debug_action_model)
    view_model = DebugActionViewModel.new
    view_model.category = debug_action_model.category
    view_model.title = debug_action_model.title
    view_model.description = debug_action_model.description
    view_model.action_name = debug_action_model.action_name
    view_model.first_param_name = debug_action_model.first_param_name
    view_model.second_param_name = debug_action_model.second_param_name
    view_model.third_param_name = debug_action_model.third_param_name
    view_model
  end
end
