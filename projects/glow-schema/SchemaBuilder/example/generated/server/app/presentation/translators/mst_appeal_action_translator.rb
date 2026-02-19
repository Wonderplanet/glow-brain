class MstAppealActionTranslator
  def self.translate(mst_appeal_action_model)
    view_model = MstAppealActionViewModel.new
    view_model.mst_appeal_id = mst_appeal_action_model.mst_appeal_id
    view_model.level = mst_appeal_action_model.level
    view_model.description_format = mst_appeal_action_model.description_format
    view_model.primary_type = mst_appeal_action_model.primary_type
    view_model.power_percentage = mst_appeal_action_model.power_percentage
    view_model.absorption_percentage = mst_appeal_action_model.absorption_percentage
    view_model.secondary_type = mst_appeal_action_model.secondary_type
    view_model.bit_target = mst_appeal_action_model.bit_target
    view_model.character_target = mst_appeal_action_model.character_target
    view_model.angry = mst_appeal_action_model.angry
    view_model.joy = mst_appeal_action_model.joy
    view_model.sad = mst_appeal_action_model.sad
    view_model.happy = mst_appeal_action_model.happy
    view_model.action_value = mst_appeal_action_model.action_value
    view_model.duration = mst_appeal_action_model.duration
    view_model.debuff_probability = mst_appeal_action_model.debuff_probability
    view_model
  end
end
