namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects
{
    public record ArtworkFragmentStatusFlags(bool IsCleared, bool IsUnReleaseQuest, bool IsOutOfTermQuest)
    {
        public bool IsEnableChallenge => !IsCleared && !IsUnReleaseQuest && !IsOutOfTermQuest;
    }
}
