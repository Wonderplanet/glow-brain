using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Views;

namespace GLOW.Scenes.InGame.Presentation.InterruptAnimation
{
    public class InterruptMangaAnimation : IInGameInterruptAnimation
    {
        readonly InGameViewController _viewController;
        readonly MangaAnimationAssetKey _assetKey;
        readonly MangaAnimationSpeed _animationSpeed;
        readonly bool _canSkip;

        MultipleSwitchHandler _viewPauseHandler;
        
        public bool CanSkip => _canSkip;
        public InterruptAnimationPriority Priority => InterruptAnimationPriorityDefinitions.MangaAnimation;

        public InterruptMangaAnimation(
            InGameViewController viewController,
            MangaAnimationAssetKey assetKey,
            MangaAnimationSpeed animationSpeed,
            bool canSkip)
        {
            _viewController = viewController;
            _assetKey = assetKey;
            _animationSpeed = animationSpeed;
            _canSkip = canSkip;
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            _viewPauseHandler?.Dispose();
            _viewPauseHandler = _viewController.PauseBattleField();
            
            if (_canSkip)
            {
                _viewController.ShowTapToSkip(0.5f);
            }
            
            try
            {
                await _viewController.PlayMangaAnimation(_assetKey, _animationSpeed, cancellationToken);
            }
            finally
            {
                if (_canSkip && _viewController != null)
                {
                    _viewController.HideTapToSkip();
                    _viewController.ResetPageScale();
                }
                
                _viewPauseHandler?.Dispose();
                _viewPauseHandler = null;
            }
        }
    }
}