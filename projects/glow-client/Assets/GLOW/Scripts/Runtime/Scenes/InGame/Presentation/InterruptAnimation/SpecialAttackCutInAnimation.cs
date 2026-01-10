using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Views;

namespace GLOW.Scenes.InGame.Presentation.InterruptAnimation
{
    public class SpecialAttackCutInAnimation : IInGameInterruptAnimation
    {
        readonly InGameViewController _viewController;
        readonly FieldObjectId _fieldObjectId;
        readonly CharacterColor _unitColor;
        readonly UnitAssetKey _unitAssetKey;
        readonly UnitAttackViewInfo _attackViewInfo;
        readonly SpecialAttackCutInSelfPauseFlag _isSelfPause;

        MultipleSwitchHandler _viewPauseHandler;
        
        public bool CanSkip => false;
        public InterruptAnimationPriority Priority => InterruptAnimationPriorityDefinitions.SpecialAttackCutIn;

        public SpecialAttackCutInAnimation(
            InGameViewController viewController,
            FieldObjectId fieldObjectId,
            CharacterColor unitColor,
            UnitAssetKey unitAssetKey,
            UnitAttackViewInfo attackViewInfo,
            SpecialAttackCutInSelfPauseFlag isSelfPause)
        {
            _viewController = viewController;
            _fieldObjectId = fieldObjectId;
            _unitColor = unitColor;
            _unitAssetKey = unitAssetKey;
            _attackViewInfo = attackViewInfo;
            _isSelfPause = isSelfPause;
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            _viewPauseHandler?.Dispose();
            _viewPauseHandler = _isSelfPause
                ? _viewController.PauseBattleField()
                : _viewController.PauseWithout(_fieldObjectId);

            try
            {
                await _viewController.PlayCutIn(_unitColor, _unitAssetKey, _attackViewInfo, cancellationToken);
            }
            finally
            {
                _viewPauseHandler?.Dispose();
                _viewPauseHandler = null;
            }
        }
    }
}