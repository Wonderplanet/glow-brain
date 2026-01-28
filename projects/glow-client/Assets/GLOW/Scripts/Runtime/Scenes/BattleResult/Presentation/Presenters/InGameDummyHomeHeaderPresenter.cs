using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.Home.Presentation.Interface;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    public class InGameDummyHomeHeaderPresenter : IHomeHeaderDelegate
    {
        public void UpdateStatus()
        {
            // 特に動作させない
        }

        public void UpdateBadgeStatus()
        {
            // 特に動作させない
        }

        public void OnStaminaRecoverButton()
        {
            // 特に動作させない
        }
        
        public void PlayExpGaugeAnimation()
        {
            // 特に動作させない
        }

        public UniTask PlayExpGaugeAnimationAsync(CancellationToken cancellationToken)
        {
            return UniTask.CompletedTask;
        }
    }
}