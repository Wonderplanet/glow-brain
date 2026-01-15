class MstSupportSkillActionTranslator
  def self.translate(mst_support_skill_action_model)
    view_model = MstSupportSkillActionViewModel.new
    view_model.mst_support_skill_id = mst_support_skill_action_model.mst_support_skill_id
    view_model.level = mst_support_skill_action_model.level
    view_model.description_format = mst_support_skill_action_model.description_format
    view_model.recast_turn = mst_support_skill_action_model.recast_turn
    view_model.skill_action_type = mst_support_skill_action_model.skill_action_type
    view_model.bit_target = mst_support_skill_action_model.bit_target
    view_model.character_target = mst_support_skill_action_model.character_target
    view_model.angry = mst_support_skill_action_model.angry
    view_model.joy = mst_support_skill_action_model.joy
    view_model.sad = mst_support_skill_action_model.sad
    view_model.happy = mst_support_skill_action_model.happy
    view_model.prob_angry = mst_support_skill_action_model.prob_angry
    view_model.prob_joy = mst_support_skill_action_model.prob_joy
    view_model.prob_sad = mst_support_skill_action_model.prob_sad
    view_model.prob_happy = mst_support_skill_action_model.prob_happy
    view_model.prob_heart = mst_support_skill_action_model.prob_heart
    view_model.action_value = mst_support_skill_action_model.action_value
    view_model.duration = mst_support_skill_action_model.duration
    view_model
  end
end
