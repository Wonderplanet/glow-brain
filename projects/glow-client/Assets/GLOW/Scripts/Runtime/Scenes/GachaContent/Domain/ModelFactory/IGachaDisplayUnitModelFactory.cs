using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.GachaContent.Domain.Model;

namespace GLOW.Scenes.GachaContent.Domain
{
    public interface IGachaDisplayUnitModelFactory
    {
        List<GachaDisplayUnitModel> CreateGachaDisplayUnitModels(MasterDataId gachaId);
    }
}
