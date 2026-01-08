using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.UnitAvatarPageView.Domain.Models;

namespace GLOW.Modules.UnitAvatarPageView.Domain.Interfaces
{
    public interface IGetSpineAvatarImageUseCase
    {
        public UnitAvatarPageModel GetImagePath(MasterDataId mstUnitId);
    }

}
