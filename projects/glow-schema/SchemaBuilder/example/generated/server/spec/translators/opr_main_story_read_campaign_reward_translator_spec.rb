require 'rails_helper'

RSpec.describe "OprMainStoryReadCampaignRewardTranslator" do
  subject { OprMainStoryReadCampaignRewardTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, opr_main_story_read_campaign_id: 1, episode_number: 2, crystal: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprMainStoryReadCampaignRewardViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.opr_main_story_read_campaign_id).to eq use_case_data.opr_main_story_read_campaign_id
      expect(view_model.episode_number).to eq use_case_data.episode_number
      expect(view_model.crystal).to eq use_case_data.crystal
    end
  end
end
