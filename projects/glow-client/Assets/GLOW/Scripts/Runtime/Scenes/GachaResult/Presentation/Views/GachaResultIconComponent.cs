using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Components;

namespace GLOW.Scenes.GachaResult.Presentation.Views
{
    public class GachaResultIconComponent : UIObject
    {
        const float AnimationTime = 0.20f;
        const float CellInterval = 0.07f;
        const float StartDelayTime = 0.4f;
        const string EffectRarityR = "GashaResult-R";
        const string EffectRaritySR = "GashaResult-SR";
        const string EffectRaritySSR = "GashaResult-SSR";
        const string EffectRarityUR = "GashaResult-UR";
        const string _appearanceAnimationName = "appear";
        const string _convertAnimationName = "GashaResultIconChange";
        [SerializeField] PlayerResourceIconComponent _component;
        [SerializeField] UIImage _newIcon;
        [SerializeField] Animator _cellPopupAnimator;
        [SerializeField] Animator _effectAnimator;
        [SerializeField] Animator _convertAnimator;
        [SerializeField] GachaResultRarityIconComponent _rarityIconComponent;
        [SerializeField] UIButtonLongPress _longPressButton;
        [SerializeField] Button _button;
        [SerializeField] GachaResultCharacterIconComponent _characterIconComponent;

        CancellationTokenSource _cancellationTokenSource;
        PlayerResourceIconViewModel _convertedPlayerResourceModel = PlayerResourceIconViewModel.Empty;
        Rarity _rarity;
        bool _isStart = false;

        public PlayerResourceIconComponent Component => _component;
        public void SetIconModel(
            GachaResultCellViewModel playerResourceModel,
            PlayerResourceIconViewModel convertedViewModel,
            int index,
            int viewModelCount,
            Action onComplete,
            Action<PlayerResourceIconViewModel> onTapped,
            Action highRaritySEAction)
        {
            _convertedPlayerResourceModel = convertedViewModel;
            var playerIconResourceModel = playerResourceModel.PlayerResourceIconViewModel;
            if (!convertedViewModel.IsEmpty())
            {
                // かけら変換された場合、かけらの情報にする
                playerIconResourceModel = convertedViewModel;
            }
            _longPressButton.PointerDown.RemoveAllListeners();
            _longPressButton.PointerDown.AddListener(() =>
            {
                onTapped?.Invoke(playerIconResourceModel);
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            });
            _characterIconComponent.SetAction(OnConvertAnimationAction);

            _component.Setup(playerResourceModel.PlayerResourceIconViewModel);
            _rarityIconComponent.SetRarity(playerResourceModel.PlayerResourceIconViewModel.Rarity);
            _rarity = playerResourceModel.PlayerResourceIconViewModel.Rarity;
            // 演出前に一度非表示にする
            _component.Hidden = true;
            _newIcon.Hidden = !playerResourceModel.IsNewUnitBadge.Value;

            // ループ再生されるため、セルが表示されるまでエフェクトを非表示にする
            _effectAnimator.gameObject.SetActive(false);

            // 一度のみ再生のためアニメーションを無効にする
            _convertAnimator.enabled = false;

            // セルアニメーション再生

            PlayCellAnimation(index, viewModelCount, onComplete, highRaritySEAction);
        }

        public void SetAvatarModel(PlayerResourceIconViewModel avatarViewModel, int index, int viewModelCount, Action onComplete)
        {
            // アイコンタップを無効にする
            _longPressButton.PointerDown.RemoveAllListeners();
            _button.interactable = false;
            _longPressButton.enabled = false;

            if(avatarViewModel == null)
            {
                this.Hidden = true;
                return;
            }

            // 演出前に一度非表示にする
            _component.Hidden = true;

            // newアイコン非表示
            _newIcon.Hidden = true;

            // アバターアイコンのセットアップ
            _component.Setup(avatarViewModel);
            _rarityIconComponent.Hidden = true;
            _rarity = avatarViewModel.Rarity;
            // ループ再生されるため、セルが表示されるまでエフェクトを非表示にする
            _effectAnimator.gameObject.SetActive(false);

            // セルアニメーション再生
            PlayCellAnimation(index, viewModelCount, onComplete);
        }

        void PlayCellAnimation(int index, int viewModelCount, Action onComplete, Action highRaritySEAction = null)
        {
            _cancellationTokenSource = new CancellationTokenSource();

            DoAsync.Invoke(_cancellationTokenSource.Token, async cancellationToken =>
            {
                // スタートまで待機
                await UniTask.WaitUntil(() => _isStart, cancellationToken: cancellationToken);

                try
                {
                    await UniTask.Delay(1, cancellationToken: cancellationToken);
                    var delayTime = CellInterval * index + StartDelayTime;

                    // アニメーション開始まで待つ
                    await UniTask.Delay(TimeSpan.FromSeconds(delayTime), cancellationToken: cancellationToken);
                    cancellationToken.ThrowIfCancellationRequested();
                    _component.Hidden = false;
                    _cellPopupAnimator.Play(_appearanceAnimationName, 0, 0);
                    
                    SoundEffectPlayer.Play(SoundEffectId.SSE_072_014);
                    
                    // SSR以上のレアリティの場合、SEを再生する
                    if (_rarity >= Rarity.SSR)
                    {
                        highRaritySEAction?.Invoke();   
                    }
                    
                    // アニメーション完了後背景エフェクトを再生する
                    await UniTask.Delay(TimeSpan.FromSeconds(AnimationTime), cancellationToken: cancellationToken);
                    cancellationToken.ThrowIfCancellationRequested();
                    PlayEffect(_rarity);

                    // 最後のセルの場合かけら変換アニメーションを再生する
                    if (index >= viewModelCount - 1)
                    {
                        onComplete.Invoke();
                    }
                }
                catch (OperationCanceledException)
                {
                    _cancellationTokenSource?.Cancel();
                }
                finally
                {
                    _cancellationTokenSource?.Dispose();
                    _cancellationTokenSource = null;
                }
            });
        }

        public void SkipCellAnimation()
        {
            _cancellationTokenSource?.Cancel();
            _component.Hidden = false;
            _cellPopupAnimator.Play(_appearanceAnimationName, 0, 1);
            PlayEffect(_rarity);
        }

        public void PlayConvertAnimation()
        {
            _convertAnimator.enabled = true;
            _convertAnimator.Play(_convertAnimationName, 0, 0);
        }

        public void StopConvertAnimation()
        {
            _convertAnimator.Play(_convertAnimationName, 0, 0);
            _convertAnimator.Update(0);
            _convertAnimator.enabled = false;
        }

        public void StartAnimation()
        {
            _isStart = true;
        }

        void PlayEffect(Rarity rarity)
        {
            string effectName = rarity switch
            {
                Rarity.R => EffectRarityR,
                Rarity.SR => EffectRaritySR,
                Rarity.SSR => EffectRaritySSR,
                Rarity.UR => EffectRarityUR,
                _ => ""
            };

            if (effectName == "") return;

            _effectAnimator.gameObject.SetActive(true);
            _effectAnimator.Play(effectName);
        }

        void OnConvertAnimationAction()
        {
            if (_convertedPlayerResourceModel.IsEmpty()) return;

            // 変換アニメーション中にアイテムアイコンを切り替える
            _component.Setup(_convertedPlayerResourceModel);
        }
    }
}
