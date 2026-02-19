class QuestSessionCreateResultTranslator
  def self.translate(quest_session_create_result_model)
    view_model = QuestSessionCreateResultViewModel.new
    view_model.quest_session = quest_session_create_result_model.quest_session
    view_model.user_ap = quest_session_create_result_model.user_ap
    view_model
  end
end
