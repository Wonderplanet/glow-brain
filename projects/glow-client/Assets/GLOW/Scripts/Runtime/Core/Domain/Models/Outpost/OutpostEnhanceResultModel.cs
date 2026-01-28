namespace GLOW.Core.Domain.Models.Outpost
{
    public record OutpostEnhanceResultModel(UserOutpostEnhanceLevelResultModel UserOutpostEnhanceLevelResultModel,
        UserParameterModel UserParameterModel)
    {
        public UserOutpostEnhanceLevelResultModel UserOutpostEnhanceLevelResultModel { get; } =
            UserOutpostEnhanceLevelResultModel;

        public UserParameterModel UserParameterModel { get; } = UserParameterModel;
    }
}
