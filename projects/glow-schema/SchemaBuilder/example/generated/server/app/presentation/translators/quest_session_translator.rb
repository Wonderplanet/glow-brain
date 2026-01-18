class QuestSessionTranslator
  def self.translate(quest_session_model)
    view_model = QuestSessionViewModel.new
    view_model.category = quest_session_model.category
    view_model.quest_id = quest_session_model.quest_id
    view_model.mst_or_opr_quest_id = quest_session_model.mst_or_opr_quest_id
    view_model.puzzle = quest_session_model.puzzle
    view_model.status = quest_session_model.status
    view_model
  end
end
