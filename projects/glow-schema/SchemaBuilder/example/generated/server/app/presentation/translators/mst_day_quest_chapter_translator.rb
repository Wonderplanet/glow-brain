class MstDayQuestChapterTranslator
  def self.translate(mst_day_quest_chapter_model)
    view_model = MstDayQuestChapterViewModel.new
    view_model.id = mst_day_quest_chapter_model.id
    view_model.background_image_path = mst_day_quest_chapter_model.background_image_path
    view_model
  end
end
