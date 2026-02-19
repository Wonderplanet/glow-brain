require 'rails_helper'

RSpec.describe "SessionCategoryTranslator" do
  subject { SessionCategoryTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, session_category: 0, opr_event_id: 1, opr_solo_live_id: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(SessionCategoryViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.session_category).to eq use_case_data.session_category
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.opr_solo_live_id).to eq use_case_data.opr_solo_live_id
    end
  end
end
