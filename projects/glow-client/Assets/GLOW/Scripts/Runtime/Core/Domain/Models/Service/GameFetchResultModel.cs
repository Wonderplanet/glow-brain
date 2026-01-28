namespace GLOW.Core.Domain.Models
{
    public record GameFetchResultModel(GameFetchModel FetchModel)
    {
        public static GameFetchResultModel Empty { get; } = new GameFetchResultModel(GameFetchModel.Empty);
    }
}
