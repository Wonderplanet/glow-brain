using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IGimmickObjectRemovingProcess
    {
        GimmickObjectRemovingProcessResult Update(IReadOnlyList<InGameGimmickObjectModel> gimmickObjects);
    }
}
