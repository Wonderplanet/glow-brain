namespace GLOW.Core.Domain.ValueObjects.Community
{
    public record CommunityUrlSchema(string Value)
    {
        public static CommunityUrlSchema Empty = new CommunityUrlSchema(string.Empty);
    }
}