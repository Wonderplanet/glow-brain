class MstLeaderSkillTranslator
  def self.translate(mst_leader_skill_model)
    view_model = MstLeaderSkillViewModel.new
    view_model.id = mst_leader_skill_model.id
    view_model.name = mst_leader_skill_model.name
    view_model
  end
end
