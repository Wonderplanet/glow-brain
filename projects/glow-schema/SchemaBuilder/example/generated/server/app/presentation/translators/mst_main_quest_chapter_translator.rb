class MstMainQuestChapterTranslator
  def self.translate(mst_main_quest_chapter_model)
    view_model = MstMainQuestChapterViewModel.new
    view_model.id = mst_main_quest_chapter_model.id
    view_model.number = mst_main_quest_chapter_model.number
    view_model.name = mst_main_quest_chapter_model.name
    view_model.release_at = mst_main_quest_chapter_model.release_at
    view_model
  end
end
