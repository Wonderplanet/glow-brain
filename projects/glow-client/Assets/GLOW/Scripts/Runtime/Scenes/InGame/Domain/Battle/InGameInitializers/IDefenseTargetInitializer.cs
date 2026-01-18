using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IDefenseTargetInitializer
    {
        DefenseTargetModel Initialize(MasterDataId defenseTargetId);
    }
}
