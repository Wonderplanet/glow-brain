require 'rails_helper'

RSpec.describe "MainStoryReadCampaignRewardTranslator" do
  subject { MainStoryReadCampaignRewardTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_main_story_read_campaign_reward_id: 0)}

    it do
      view_model = subject
      expect(view_model.is_a?(MainStoryReadCampaignRewardViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_main_story_read_campaign_reward_id).to eq use_case_data.opr_main_story_read_campaign_reward_id
    end
  end
end
