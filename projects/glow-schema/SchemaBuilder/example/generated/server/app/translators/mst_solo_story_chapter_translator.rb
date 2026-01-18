class MstSoloStoryChapterTranslator
  def self.translate(mst_solo_story_chapter_model)
    view_model = MstSoloStoryChapterViewModel.new
    view_model.id = mst_solo_story_chapter_model.id
    view_model.mst_character_variant_id = mst_solo_story_chapter_model.mst_character_variant_id
    view_model.name = mst_solo_story_chapter_model.name
    view_model.release_at = mst_solo_story_chapter_model.release_at
    view_model
  end
end
