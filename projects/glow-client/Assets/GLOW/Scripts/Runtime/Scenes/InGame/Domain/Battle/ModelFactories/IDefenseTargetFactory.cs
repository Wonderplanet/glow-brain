using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IDefenseTargetFactory
    {
        DefenseTargetModel GenerateDefenseTarget(MstDefenseTargetModel mstDefenseTargetModel);
    }
}
