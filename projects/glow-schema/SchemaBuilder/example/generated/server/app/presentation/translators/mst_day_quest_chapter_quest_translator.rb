class MstDayQuestChapterQuestTranslator
  def self.translate(mst_day_quest_chapter_quest_model)
    view_model = MstDayQuestChapterQuestViewModel.new
    view_model.id = mst_day_quest_chapter_quest_model.id
    view_model.day_of_week = mst_day_quest_chapter_quest_model.day_of_week
    view_model.mst_day_quest_chapter_id = mst_day_quest_chapter_quest_model.mst_day_quest_chapter_id
    view_model.mst_day_quest_id = mst_day_quest_chapter_quest_model.mst_day_quest_id
    view_model
  end
end
