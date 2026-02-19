using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Tutorial;

namespace GLOW.Scenes.GachaList.Domain.Applier
{
    public interface IGachaDrawResultApplier
    {
        void UpdateGachaResult(GachaDrawResultModel resultModel);
    }
}
