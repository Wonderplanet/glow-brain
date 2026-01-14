class MstLeaderSkillActionTranslator
  def self.translate(mst_leader_skill_action_model)
    view_model = MstLeaderSkillActionViewModel.new
    view_model.mst_leader_skill_id = mst_leader_skill_action_model.mst_leader_skill_id
    view_model.description_format = mst_leader_skill_action_model.description_format
    view_model.skill_action_type = mst_leader_skill_action_model.skill_action_type
    view_model.bit_target = mst_leader_skill_action_model.bit_target
    view_model.character_target = mst_leader_skill_action_model.character_target
    view_model.angry = mst_leader_skill_action_model.angry
    view_model.joy = mst_leader_skill_action_model.joy
    view_model.sad = mst_leader_skill_action_model.sad
    view_model.happy = mst_leader_skill_action_model.happy
    view_model.condition = mst_leader_skill_action_model.condition
    view_model.condition_value = mst_leader_skill_action_model.condition_value
    view_model.action_value = mst_leader_skill_action_model.action_value
    view_model.debuff_probability = mst_leader_skill_action_model.debuff_probability
    view_model
  end
end
