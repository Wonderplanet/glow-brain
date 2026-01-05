using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Title.Domains.ValueObjects;

namespace GLOW.Scenes.Title.Presentations.ViewModels
{
    public record ApplicationInfoViewModel(
        ApplicationVersion ApplicationVersion,
        UserMyId UserMyId)
    {
        public ApplicationVersion ApplicationVersion { get; } = ApplicationVersion;
        public UserMyId UserMyId { get; } = UserMyId;
    }
}