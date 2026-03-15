require 'rails_helper'

RSpec.describe "SoloLiveSessionCreateResultTranslator" do
  subject { SoloLiveSessionCreateResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, solo_live_session: 0, user_ap: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(SoloLiveSessionCreateResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.solo_live_session).to eq use_case_data.solo_live_session
      expect(view_model.user_ap).to eq use_case_data.user_ap
    end
  end
end
