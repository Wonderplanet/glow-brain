using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class DefenseTargetInitializer : IDefenseTargetInitializer
    {
        [Inject] IMstDefenseTargetDataRepository MstDefenseTargetDataRepository { get; }
        [Inject] IDefenseTargetFactory DefenseTargetFactory { get; }

        DefenseTargetModel IDefenseTargetInitializer.Initialize(MasterDataId defenseTargetId)
        {
            if (defenseTargetId.IsEmpty())
            {
                return DefenseTargetModel.Empty;
            }

            var mstDefenseTargetModel = MstDefenseTargetDataRepository.GetMstDefenseTargetModel(defenseTargetId);
            var defenseTargetModel = DefenseTargetFactory.GenerateDefenseTarget(mstDefenseTargetModel);
            return defenseTargetModel;
        }
    }
}
