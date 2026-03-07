using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Views;
using WPFramework.Domain.Modules;

namespace GLOW.Scenes.InGame.Presentation.InterruptAnimation
{
    public class BossAppearanceAnimation : IInGameInterruptAnimation
    {
        readonly InGameViewController _viewController;
        readonly CharacterUnitModel _bossUnitModel;
        readonly IViewCoordinateConverter _viewCoordinateConverter;
        readonly IBackgroundMusicPlayable _backgroundMusicPlayable;
        readonly BGMAssetKey _bossBGMAssetKey;
        
        MultipleSwitchHandler _viewPauseHandler;
        
        public bool CanSkip => false;
        public InterruptAnimationPriority Priority => InterruptAnimationPriorityDefinitions.BossAppearance;

        public BossAppearanceAnimation(
            InGameViewController viewController,
            CharacterUnitModel bossUnitModel,
            IViewCoordinateConverter viewCoordinateConverter,
            IBackgroundMusicPlayable backgroundMusicPlayable,
            BGMAssetKey bossBGMAssetKey)
        {
            _viewController = viewController;
            _bossUnitModel = bossUnitModel;
            _viewCoordinateConverter = viewCoordinateConverter;
            _backgroundMusicPlayable = backgroundMusicPlayable;
            _bossBGMAssetKey = bossBGMAssetKey;
        }

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            try
            {
                _viewPauseHandler = _viewController.PauseWithoutDarknessClear();

                // NOTE: BGMを強敵出現時に変更する。未設定の場合はステージBGMが流れたままにする
                if (!_bossBGMAssetKey.IsEmpty())
                {
                    _backgroundMusicPlayable.Play(_bossBGMAssetKey.Value);
                }

                // ボス出現前兆SE
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_016);

                // スクロール
                var targetPos = _viewCoordinateConverter.ToFieldViewCoord(BattleSide.Enemy, _bossUnitModel.Pos);
                await _viewController.ScrollPage(targetPos, 0.5f, cancellationToken);

                // 暗闇コマの場合は晴れるのを待つ
                await WaitDarknessKomaClear(targetPos, cancellationToken);

                // コマ拡大
                await _viewController.ScalePage(targetPos, 1.3f, 0.05f, cancellationToken);

                // ボス出現SE
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_017);

                // ボスを召喚
                _viewController.OnSummonCharacter(_bossUnitModel, null);

                _viewPauseHandler?.Dispose();
                _viewPauseHandler = null;
            }
            finally
            {
                _viewPauseHandler?.Dispose();
                _viewPauseHandler = null;
            }
        }

        async UniTask WaitDarknessKomaClear(FieldViewCoordV2 pos, CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(() => _viewController.IsDarknessKomaCleared(pos), cancellationToken: cancellationToken);
        }
    }
}