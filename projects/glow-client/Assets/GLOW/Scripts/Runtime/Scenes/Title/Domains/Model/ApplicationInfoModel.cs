using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Title.Domains.ValueObjects;

namespace GLOW.Scenes.Title.Domains.Model
{
    public record ApplicationInfoModel(
        ApplicationVersion ApplicationVersion,
        UserMyId UserMyId)
    {
        public static ApplicationInfoModel Empty { get; } = new(
            ApplicationVersion.Empty,
            UserMyId.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}