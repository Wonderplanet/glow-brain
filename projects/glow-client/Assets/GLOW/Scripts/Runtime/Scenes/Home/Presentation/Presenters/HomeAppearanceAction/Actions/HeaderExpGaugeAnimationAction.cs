using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.Home.Presentation.Interface;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// ヘッダーの経験値ゲージアニメーション
    /// </summary>
    public class HeaderExpGaugeAnimationAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<HeaderExpGaugeAnimationAction> { }

        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            await HomeHeaderDelegate.PlayExpGaugeAnimationAsync(cancellationToken);
        }
    }
}
