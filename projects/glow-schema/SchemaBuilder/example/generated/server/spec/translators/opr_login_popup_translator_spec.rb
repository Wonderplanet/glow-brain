require 'rails_helper'

RSpec.describe "OprLoginPopupTranslator" do
  subject { OprLoginPopupTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, priority: 1, transition_type: 2, banner_path: 3, start_at: 4, end_at: 5, opr_announcement_id: 6, opr_gacha_id: 7, opr_event_id: 8)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprLoginPopupViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.priority).to eq use_case_data.priority
      expect(view_model.transition_type).to eq use_case_data.transition_type
      expect(view_model.banner_path).to eq use_case_data.banner_path
      expect(view_model.start_at).to eq use_case_data.start_at
      expect(view_model.end_at).to eq use_case_data.end_at
      expect(view_model.opr_announcement_id).to eq use_case_data.opr_announcement_id
      expect(view_model.opr_gacha_id).to eq use_case_data.opr_gacha_id
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
    end
  end
end
