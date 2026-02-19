using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class ChangeBattleSpeedUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }

        public BattleSpeed ChangeBattleSpeed()
        {
            if (InGameScene.IsBattleOver) return InGameScene.CurrentBattleSpeed;

            var battleSpeeds = InGameScene.BattleSpeedList;
            
            var newBattleSpeed = (BattleSpeed)(((int)InGameScene.CurrentBattleSpeed + 1) % battleSpeeds.Count);

            InGamePreferenceRepository.InGameBattleSpeed = newBattleSpeed;

            InGameScene.CurrentBattleSpeed = newBattleSpeed;

            return newBattleSpeed;
        }
    }
}
