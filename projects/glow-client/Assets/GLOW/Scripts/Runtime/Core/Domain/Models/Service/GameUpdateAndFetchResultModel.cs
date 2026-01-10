namespace GLOW.Core.Domain.Models
{
    public record GameUpdateAndFetchResultModel(GameFetchModel FetchModel, GameFetchOtherModel FetchOtherModel)
    {
        public GameFetchModel FetchModel { get; } = FetchModel;
        public GameFetchOtherModel FetchOtherModel { get; } = FetchOtherModel;
    }
}
