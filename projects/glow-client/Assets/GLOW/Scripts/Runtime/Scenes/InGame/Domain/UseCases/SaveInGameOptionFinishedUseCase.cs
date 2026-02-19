using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class SaveInGameOptionFinishedUseCase
    {
        [Inject] ISpecialAttackCutInLogRepository SpecialAttackCutInLogRepository { get; }

        public void  SaveInGameOptionFinished(List<MasterDataId> playedSpecialAttackUnitIds)
        {
            var specialAttackCutInLog = SpecialAttackCutInLogRepository.Get();
            
            var updatedSpecialAttackCutInLog = specialAttackCutInLog with
            {
                PlayedSpecialAttackUnitIds = playedSpecialAttackUnitIds
            };
            
            SpecialAttackCutInLogRepository.Save(updatedSpecialAttackCutInLog);
        }
    }
}
