using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Presenters;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Views;

namespace GLOW.Scenes.InGame.Presentation.InterruptAnimation
{
    public class SpecialUnitSummonAnimation : IInGameInterruptAnimation
    {
        readonly InGamePresenter _presenter;
        readonly InGameViewController _viewController;
        readonly KomaExpander _komaExpander;
        readonly IViewCoordinateConverter _viewCoordinateConverter;
        readonly SpecialUnitModel _specialUnitModel;
        readonly CoordinateRange _summonableRange;
        readonly CancellationToken _cancellationTokenOnViewDestroy;

        MultipleSwitchHandler _viewPauseHandler;
        UniTaskCompletionSource _completionSource = new UniTaskCompletionSource();

        public bool CanSkip => false;
        public InterruptAnimationPriority Priority => InterruptAnimationPriorityDefinitions.SpecialUnitSummon;

        public SpecialUnitSummonAnimation(
            InGamePresenter presenter,
            InGameViewController viewController,
            KomaExpander komaExpander,
            IViewCoordinateConverter viewCoordinateConverter,
            SpecialUnitModel specialUnitModel,
            CoordinateRange summonableRange,
            CancellationToken cancellationTokenOnViewDestroy)
        {
            _presenter = presenter;
            _viewController = viewController;
            _komaExpander = komaExpander;
            _viewCoordinateConverter = viewCoordinateConverter;
            _specialUnitModel = specialUnitModel;
            _summonableRange = summonableRange;
            _cancellationTokenOnViewDestroy = cancellationTokenOnViewDestroy;
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            try
            {
                // スペシャルユニット発動時は前面表示を無しにする
                _komaExpander.ResetKomaExpanding();

                if (_specialUnitModel.SpecialAttack.MainAttackElement.AttackType == AttackType.Deck)
                {
                    // Page内を対象としないためスペシャルユニット以外全コマ暗転
                    _viewController.ShowIndividualBlackCurtain(cancellationToken).Forget();
                }
                else
                {
                    // 効果範囲をkoma側の座標に合わせるためにFieldView座標に調整。進行方向の認識が変わるためmaxとminを入れ替える
                    var minFieldCoord = new FieldCoordV2(_summonableRange.Min, _specialUnitModel.Pos.Y);
                    var maxFieldCoord = new FieldCoordV2(_summonableRange.Max, _specialUnitModel.Pos.Y);
                    var minFieldViewCoord = _viewCoordinateConverter.ToFieldViewCoord(minFieldCoord);
                    var maxFieldViewCoord = _viewCoordinateConverter.ToFieldViewCoord(maxFieldCoord);
                    var intersectionThreshold = 0.0001f; // 浮動小数点数の計算誤差を考慮した閾値
                    var fieldViewCoordinateRange = new CoordinateRange(
                        maxFieldViewCoord.X + intersectionThreshold,
                        minFieldViewCoord.X - intersectionThreshold);

                    _viewController.ShowHighlightKomaWithinSpecialCoordinateRange(fieldViewCoordinateRange);
                }

                _viewPauseHandler = _viewController.PauseWithout(_specialUnitModel.Id);

                await using var _ = cancellationToken.Register(() => _completionSource.TrySetCanceled());
                await _completionSource.Task;
            }
            finally
            {
                _viewPauseHandler?.Dispose();
                _viewPauseHandler = null;

                _viewController.HideIndividualBlackCurtain(_cancellationTokenOnViewDestroy).Forget();
                _viewController.HideKomaHighlightWithinSpecialCoordinateRange();
            }
        }

        public void EndAnimation()
        {
            _completionSource.TrySetResult();
        }
    }
}
