using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaAnim.Presentation.Views.Parts
{
    public class GachaAnimStartComponent : UIObject
    {
        static readonly int Rare = Animator.StringToHash("Rare");
        static readonly int RareUp = Animator.StringToHash("RareUp");
        static readonly int Num = Animator.StringToHash("kakuteiNum");
        static readonly int AnimStart = Animator.StringToHash("Start");
        static readonly int AnimStartTap = Animator.StringToHash("StartTap");
        static readonly int Kakutei = Animator.StringToHash("Kakutei");
        static readonly int RainbowBackground = Animator.StringToHash("KakuteiRare");

        [SerializeField] Animator _animator;
        [SerializeField] Animator _startFukidashiAnimator;
        [SerializeField] List<GachaAnimIconCellComponent> _iconCellComponents;
        [SerializeField] GachaAnimRarityUpVariationComponent _variationURComponents;
        [SerializeField] GachaAnimRarityUpVariationComponent _variationSSRComponents;
        [SerializeField] GachaAnimRarityUpVariationComponent _variationSRComponents;
        [SerializeField] GachaAnimRarityUpVariationComponent _variationRComponents;
        [SerializeField] Button _animationStartButton;
        [SerializeField] Button _animationSkipButton;
        
        public GachaAnimRarityUpVariationComponent VariationURComponents => _variationURComponents;
        public GachaAnimRarityUpVariationComponent VariationSSRComponents => _variationSSRComponents;
        public GachaAnimRarityUpVariationComponent VariationSRComponents => _variationSRComponents;
        public GachaAnimRarityUpVariationComponent VariationRComponents => _variationRComponents;
        
        bool _isEndStartAnimation = false;
        bool _isEndStartTapAnimation = false;
        bool _isStart = false;
        bool _isSkip = false;
        bool _isAllSkip = false;
        bool _isPlayHighRaritySE = false;
        int _rarityInt;
        Action _onStartAction;

        public void Setup(GachaAnimStartViewModel viewModel, Action onStartAction)
        {
            _animationStartButton.onClick.AddListener(OnAnimationStart);
            _animationSkipButton.onClick.AddListener(OnAnimationSkip);
            
            // セルのレア度を設定 レアリティとアイテムかどうかのフラグを設定
            _animator.SetInteger(Rare, (int)viewModel.StartRarity + 1);
            _animator.SetInteger(RareUp, (int)viewModel.EndRarity + 1);
            _animator.SetInteger(Num, viewModel.Count);
            _rarityInt = (int)viewModel.StartRarity + 1;
            _onStartAction = onStartAction;

            for (int i = 0; i < viewModel.Count; i++)
            {
                _iconCellComponents[i].Setup(viewModel.IconInfos[i], PlayHighRaritySoundEffect);
            }

            if (viewModel.EndRarity >= Rarity.UR)
            {
                _animator.SetInteger(RainbowBackground, 1);
            }
        }

        public async UniTask PlayAnimation(CancellationToken cancellationToken)
        {
            // 演出開始アニメーションを開始
            _animator.SetTrigger(AnimStart);
            // 1f待って吹き出しレア度表示を切り替え
            await UniTask.DelayFrame(1, cancellationToken: cancellationToken);
            _startFukidashiAnimator.SetInteger(Rare, _rarityInt);
            // スタートのフラグをリセットし、演出開始のタップ待ち
            _isStart = false;
            await UniTask.WaitUntil(() =>  _isStart || _isAllSkip, cancellationToken: cancellationToken);
            
            // ボタンの切り替え
            _animationStartButton.gameObject.SetActive(false);
            _animationSkipButton.gameObject.SetActive(true);
            
            // タップアニメーションを開始
            _animator.SetTrigger(AnimStartTap);
            // タップのフラグリセット
            _isSkip = false;
            // タップアニメーション終了待ち
            await UniTask.WaitUntil(() => _isEndStartTapAnimation || _isAllSkip, cancellationToken: cancellationToken);
            // 全スキップボタンを表示する
            _onStartAction?.Invoke();
            _isSkip = false;

            // ガシャのレアリティ昇格アニメーション待機
            await UniTask.WaitUntil(() => _isEndStartAnimation || _isSkip || _isAllSkip, cancellationToken: cancellationToken);

            // ガシャ排出物一覧のレアリティ表示し、タップを待つ
            _animator.SetTrigger(Kakutei);
            _isPlayHighRaritySE = false;
            _isSkip = false;
            await UniTask.WaitUntil(() => _isSkip || _isAllSkip, cancellationToken: cancellationToken);
            Hidden = true;
        }

        public void OnAnimationStart()
        {
            _isStart = true;
        }

        public void OnAnimationSkip()
        {
            // スキップ時にSEを止める
            if (!_isSkip) StopSoundEffectAndPlaySelect();
            
            _isSkip = true;
        }

        public void SkipAll()
        {
            // スキップ時にSEを止める
            if (!_isAllSkip) StopSoundEffectAndPlaySelect();
            
            _isAllSkip = true;
        }
        
        void StopSoundEffectAndPlaySelect()
        {
            SoundEffectPlayer.Stop();
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
        }

        void EndStartTapAnimation()
        {
            // タップアニメーション終了時にAnimationからEventFunctionで呼ばれる
            _isEndStartTapAnimation = true;
        }

        void EndStartAnimation()
        {
            // 開始アニメーション終了時にAnimationからEventFunctionで呼ばれる
            _isEndStartAnimation = true;
        }
        
        void PlayHighRaritySoundEffect(SoundEffectId id)
        {
            if (_isPlayHighRaritySE) return;
            
            _isPlayHighRaritySE = true;
            SoundEffectPlayer.Play(id);
        }
    }
}
