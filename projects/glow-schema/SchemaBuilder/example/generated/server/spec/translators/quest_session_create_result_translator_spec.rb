require 'rails_helper'

RSpec.describe "QuestSessionCreateResultTranslator" do
  subject { QuestSessionCreateResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, quest_session: 0, user_ap: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(QuestSessionCreateResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.quest_session).to eq use_case_data.quest_session
      expect(view_model.user_ap).to eq use_case_data.user_ap
    end
  end
end
