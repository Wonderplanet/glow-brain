using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Constants;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class BattleSpeedInitializer : IBattleSpeedInitializer
    {
        [Inject] IHeldPassEffectRepository HeldPassEffectRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        BattleSpeedInitializationResult IBattleSpeedInitializer.Initialize(BattleSpeed preferenceBattleSpeed)
        {
            var existBattleSpeedPassEffect = HeldPassEffectRepository.GetHeldPassEffectListModel()
                .IsValidPassEffect(
                    ShopPassEffectType.ChangeBattleSpeed, 
                    TimeProvider.Now);

            var currentBattleSpeed = preferenceBattleSpeed;
            if (currentBattleSpeed == BattleSpeed.x3 && !existBattleSpeedPassEffect)
            {
                currentBattleSpeed = BattleSpeed.x2;
            }

            var battleSpeedList = new List<BattleSpeed>()
            {
                BattleSpeed.x1,
                BattleSpeed.x1_5,
                BattleSpeed.x2
            };
            
            if (existBattleSpeedPassEffect)
            {
                battleSpeedList.Add(BattleSpeed.x3);
            }
            
            return new BattleSpeedInitializationResult(currentBattleSpeed, battleSpeedList);
        }
    }
}