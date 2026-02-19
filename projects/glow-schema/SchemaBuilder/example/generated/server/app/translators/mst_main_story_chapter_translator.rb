class MstMainStoryChapterTranslator
  def self.translate(mst_main_story_chapter_model)
    view_model = MstMainStoryChapterViewModel.new
    view_model.id = mst_main_story_chapter_model.id
    view_model.number = mst_main_story_chapter_model.number
    view_model.name = mst_main_story_chapter_model.name
    view_model.release_at = mst_main_story_chapter_model.release_at
    view_model.publication_at = mst_main_story_chapter_model.publication_at
    view_model
  end
end
