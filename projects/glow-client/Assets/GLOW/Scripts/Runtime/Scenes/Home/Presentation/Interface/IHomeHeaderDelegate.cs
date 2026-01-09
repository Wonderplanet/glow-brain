using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Scenes.Home.Presentation.Interface
{
    public interface IHomeHeaderDelegate
    {
        void UpdateStatus();
        void UpdateBadgeStatus();
        void OnStaminaRecoverButton();
        void PlayExpGaugeAnimation();
        UniTask PlayExpGaugeAnimationAsync(CancellationToken cancellationToken);
    }
}
