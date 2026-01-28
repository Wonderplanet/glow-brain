require 'rails_helper'

RSpec.describe "OprMainStoryReadCampaignTranslator" do
  subject { OprMainStoryReadCampaignTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_main_story_chapter_id: 1, start_at: 2, end_at: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprMainStoryReadCampaignViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_main_story_chapter_id).to eq use_case_data.mst_main_story_chapter_id
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
    end
  end
end
