require 'rails_helper'

RSpec.describe "OprPointUpTimeTranslator" do
  subject { OprPointUpTimeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, opr_event_id: 0, percentage: 1, start_time: 2, end_time: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprPointUpTimeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.percentage).to eq use_case_data.percentage
      expect(view_model.start_time).to eq use_case_data.start_time
      expect(view_model.end_time).to eq use_case_data.end_time
    end
  end
end
