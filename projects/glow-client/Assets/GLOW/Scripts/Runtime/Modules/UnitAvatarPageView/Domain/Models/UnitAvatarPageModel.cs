using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.UnitAvatarPageView.Domain.Models
{
    public record UnitAvatarPageModel(UnitImageAssetPath ImagePath, CharacterColor Color, PhantomizedFlag IsPhantomized);
}
