using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Presentation.UI.UIEffect;
using GLOW.Scenes.InGame.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Components;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class DeckCharacterComponent : UIObject
    {
        public enum TapAction
        {
            Summon,
            SpecialAttack,
            SpecialUnitSpecialAttack
        }

        static readonly int CoolTimeAnimationParameter_summoned = Animator.StringToHash("summoned");
        static readonly int CoolTimeAnimationParameter_full = Animator.StringToHash("full");
        static readonly int CoolTimeAnimationParameter_fullSpecialAttack = Animator.StringToHash("fullSpecialAttack");
        static readonly int CoolTimeAnimationParameter_specialAttackReady = Animator.StringToHash("specialAttackReady");
        static readonly int CoolTimeAnimationParameter_spUnit = Animator.StringToHash("spUnit");
        static readonly int CoolTimeAnimationParameter_spSpecialAttackReady = Animator.StringToHash("spSpecialAttackReady");

        static readonly int RootAnimationTrigger_canSummon = Animator.StringToHash("canSummon");
        static readonly int RootAnimationTrigger_canUseSpecialAttack = Animator.StringToHash("canUseSpecialAttack");
        static readonly int RootAnimationTrigger_spUnit = Animator.StringToHash("spUnit");

        [SerializeField] Button _button;
        [Header("編成ロック")]
        [SerializeField] UIImage _lockImage;

        [Header("キャラクターアタッチ")]
        [SerializeField] UIImage _rarityFrame;
        [SerializeField] Sprite _rarityR;
        [SerializeField] Sprite _raritySR;
        [SerializeField] Sprite _raritySSR;
        [SerializeField] Sprite _rarityUR;
        [Header("キャラクターアタッチ")]
        [SerializeField] GameObject _root;
        [SerializeField] Animator _rootAnimator;
        [SerializeField] GameObject _emptyGameObject;
        [SerializeField] UIImage _characterImage;
        [SerializeField] UIImage _characterSpecialAttackImage;
        [Header("召喚コスト")]
        [SerializeField] UIObject _summonCostUI;
        [SerializeField] UIText _summonCostText;
        [SerializeField] Image _summonCoolTimeGaugeImage;
        [Header("必殺技クールタイム")]
        [SerializeField] UIObject _coolTimeGaugeUI;
        [SerializeField] Image _coolTimeGaugeImage;
        [SerializeField] Animator _coolTimeAnimator;
        [Header("ロール/カラー")]
        [SerializeField] CharaRoleIcon _roleIcon;
        [SerializeField] CharacterColorIcon _colorIcon;
        [Header("ステータス変更")]
        [SerializeField] UIImage _blackImage;
        [SerializeField] UIImage _specialAttackReadyImage;
        [Header("デッキ切替・長押し")]
        [SerializeField] DeckSwipeDetector _deckSwipeDetector;
        [SerializeField] UIButtonLongPress _longPress;

        UIEffectManager _uiEffectManager;

        DeckUnitViewModel _deckUnitViewModel;
        bool _isSwitchDeckMode;
        bool _isFront; // デッキ前後切り替えモードのときに前面側か

        bool _isSwipeAnimationing;
        bool _isSwipe => _deckSwipeDetector.IsSwiping || IsSwipeAnimationing;
        public bool IsSwipeAnimationing
        {
            get => _isSwipeAnimationing;
            set => _isSwipeAnimationing = value;
        }

        public MasterDataId CharacterId { get; private set; }

        public bool IsSwitchDeckMode
        {
            get => _isSwitchDeckMode;
            set
            {
                _isSwitchDeckMode = value;
                UpdateInteractable();
            }
        }

        public bool IsFront
        {
            get => _isFront;
            set
            {
                _isFront = value;
                UpdateInteractable();
            }
        }

        public Action<MasterDataId, TapAction> OnTapped { get; set; }

        public Action OnSwipedLeft
        {
            get => _deckSwipeDetector.OnSwipedLeft;
            set => _deckSwipeDetector.OnSwipedLeft = value;
        }

        public Action OnSwipedRight
        {
            get => _deckSwipeDetector.OnSwipedRight;
            set => _deckSwipeDetector.OnSwipedRight = value;
        }

        public UIButtonLongPress OnLongPress => _longPress;

        public void Initialize(DeckUnitViewModel deckUnitViewModel, bool isTwoRow, UIEffectManager uiEffectManager)
        {
            _uiEffectManager = uiEffectManager;

            _deckUnitViewModel = deckUnitViewModel;

            bool isEmpty = deckUnitViewModel.IsEmpty();

            CharacterId = deckUnitViewModel.CharacterId;

            if (!isEmpty)
            {
                if (isTwoRow)
                {
                    UISpriteUtil.LoadSpriteWithFade(
                        _characterImage.Image, 
                        deckUnitViewModel.IconAssetPath.ToAssetPath());
                    
                    UISpriteUtil.LoadSpriteWithFade(
                        _characterSpecialAttackImage.Image, 
                        deckUnitViewModel.SpecialAttackIconAssetPath.ToAssetPath());
                }
                else
                {
                    UISpriteUtil.LoadSpriteWithFade(
                        _characterImage.Image, 
                        deckUnitViewModel.IconAssetPath.ToLongIconAssetPath());
                    
                    UISpriteUtil.LoadSpriteWithFade(
                        _characterSpecialAttackImage.Image, 
                        deckUnitViewModel.SpecialAttackIconAssetPath.ToLongIconAssetPath());
                }
            }

            _rarityFrame.Sprite = deckUnitViewModel.Rarity switch
            {
                Rarity.R => _rarityR,
                Rarity.SR => _raritySR,
                Rarity.SSR => _raritySSR,
                Rarity.UR => _rarityUR,
                _ => _rarityR,
            };
            _roleIcon.SetupCharaRoleIcon(deckUnitViewModel.RoleType);
            _colorIcon.SetupCharaColorIcon(deckUnitViewModel.CharacterColor);

            _coolTimeGaugeUI.Hidden = true;
            _summonCostUI.Hidden = false;
            _summonCostText.SetText(deckUnitViewModel.SummonCost.ToString());

            _root.SetActive(!isEmpty);
            _emptyGameObject.SetActive(isEmpty);
            _lockImage.Hidden = !deckUnitViewModel.IsLock.Value;

            if (_deckUnitViewModel.RoleType == CharacterUnitRoleType.Special)
            {
                UpdateCoolTimeGaugeSpecialUnit(deckUnitViewModel);
                UpdateImageSpecialAttackOnly(deckUnitViewModel);
            }
            else
            {
                UpdateCoolTimeGauge(deckUnitViewModel);
                UpdateImage(deckUnitViewModel);
            }
            UpdateInteractable();
            
            _deckSwipeDetector.OnSwipedRight = CancelLongPress;
            _deckSwipeDetector.OnSwipedLeft = CancelLongPress;
        }

        public void UpdateDeckUnit(DeckUnitViewModel deckUnitViewModel)
        {
            PlaySoundEffectIfNeeded(_deckUnitViewModel, deckUnitViewModel);
            
            _deckUnitViewModel = deckUnitViewModel;

            _summonCostText.SetText(deckUnitViewModel.SummonCost.ToString());

            if (_deckUnitViewModel.RoleType == CharacterUnitRoleType.Special)
            {
                UpdateCoolTimeGaugeSpecialUnit(deckUnitViewModel);
                UpdateImageSpecialAttackOnly(deckUnitViewModel);
            }
            else
            {
                UpdateCoolTimeGauge(deckUnitViewModel);
                UpdateImage(deckUnitViewModel);
            }
            UpdateInteractable();
        }

        void UpdateInteractable()
        {
            bool isEmpty = _deckUnitViewModel.IsEmpty();

            _button.interactable = !isEmpty && (!IsSwitchDeckMode || IsFront);
        }

        void UpdateImage(DeckUnitViewModel deckUnitViewModel)
        {
            _blackImage.Hidden = BlackImageHidden();

            var cantSpecialAttack = !(_deckUnitViewModel.IsSummoned
                                      && _deckUnitViewModel.IsSpecialAttackReady);
            _specialAttackReadyImage.Hidden = cantSpecialAttack;

            var canSummon = CanSummon(_deckUnitViewModel);
            var canUseSpecialAttack = CanUseSpecialAttack(_deckUnitViewModel);
            var specialAttackReady =  deckUnitViewModel.IsSpecialAttackReady;
            var fullSpecialAttack = _coolTimeGaugeImage.fillAmount >= 1f;

            _rootAnimator.SetBool(RootAnimationTrigger_canSummon, canSummon);
            _rootAnimator.SetBool(RootAnimationTrigger_canUseSpecialAttack, canUseSpecialAttack);
            _rootAnimator.SetBool(RootAnimationTrigger_spUnit, false);
            _rootAnimator.SetBool(CoolTimeAnimationParameter_specialAttackReady,specialAttackReady);
            _rootAnimator.SetBool(CoolTimeAnimationParameter_fullSpecialAttack, fullSpecialAttack);
            _rootAnimator.SetBool(CoolTimeAnimationParameter_summoned, deckUnitViewModel.IsSummoned);
        }

        void UpdateImageSpecialAttackOnly(DeckUnitViewModel deckUnitViewModel)
        {
            _blackImage.Hidden = BlackImageHiddenSpecialUnit();

            var cantSpecialAttack = !(_deckUnitViewModel.IsSummoned
                                      && _deckUnitViewModel.IsSpecialAttackReady);
            _specialAttackReadyImage.Hidden = cantSpecialAttack;

            var canUseSpecialAttack = CanSpecialUnitUseSpecialAttack(deckUnitViewModel);
            var specialAttackReady = deckUnitViewModel.IsSpecialAttackReady;

            _rootAnimator.SetBool(RootAnimationTrigger_canUseSpecialAttack, canUseSpecialAttack);
            _rootAnimator.SetBool(RootAnimationTrigger_spUnit, true);
            _rootAnimator.SetBool(CoolTimeAnimationParameter_summoned, deckUnitViewModel.IsSummoned);

            if (specialAttackReady)
            {
                _rootAnimator.SetTrigger(CoolTimeAnimationParameter_spSpecialAttackReady);
            }
        }

        bool BlackImageHidden()
        {
            var result = false;
            if (_deckUnitViewModel.IsSummoned)
            {
                if (_deckUnitViewModel.IsSpecialAttackReady) result= true;
                if (!_deckUnitViewModel.RemainingSpecialAttackCoolTime.IsZero()) result= true;
                result= false;
            }
            else
            {
                if (_deckUnitViewModel.IsLackOfBattlePoint &&
                    _deckUnitViewModel.RemainingSummonCoolTime.IsZero()) result= true;

                if (!_deckUnitViewModel.RemainingSummonCoolTime.IsZero()) result = true; //撃破されて再召喚ゲージ蓄積中
            }

            return !result; //Hidden = trueで非表示なので、反転して返す
        }

        bool BlackImageHiddenSpecialUnit()
        {
            // 召喚中でアイコンの必殺技発動までの間と必殺技発動開始演出中は暗くしない
            if (_deckUnitViewModel.IsSummoned &&
                !_rootAnimator.GetCurrentAnimatorStateInfo(0).IsName("Special@WaitHide-Sp")) return true;

            if (_deckUnitViewModel.IsSpecialAttackReady ||
                _deckUnitViewModel.IsLackOfBattlePoint ||
                !_deckUnitViewModel.CanSummonAnySpecialUnit) return false;

            return true;
        }

        void UpdateCoolTimeGauge(DeckUnitViewModel deckUnitViewModel)
        {
            if (_deckUnitViewModel.IsSummoned)
            {
                // 召喚中は必殺ワザ使用クールタイムを表示
                _coolTimeGaugeImage.fillAmount = !_deckUnitViewModel.SpecialAttackCoolTime.IsZero()
                    ? 1f - _deckUnitViewModel.RemainingSpecialAttackCoolTime / _deckUnitViewModel.SpecialAttackCoolTime
                    : 1f;
            }
            else
            {
                // 召喚クールタイムを表示
                _summonCoolTimeGaugeImage.fillAmount = !_deckUnitViewModel.SummonCoolTime.IsZero()
                    ? 1f - _deckUnitViewModel.RemainingSummonCoolTime / _deckUnitViewModel.SummonCoolTime
                    : 1f;
            }

            _coolTimeAnimator.SetBool(CoolTimeAnimationParameter_summoned, deckUnitViewModel.IsSummoned);
            _coolTimeAnimator.SetBool(CoolTimeAnimationParameter_specialAttackReady, deckUnitViewModel.IsSpecialAttackReady);

            _coolTimeAnimator.SetBool(CoolTimeAnimationParameter_full, _summonCoolTimeGaugeImage.fillAmount >= 1f);
            _coolTimeAnimator.SetBool(CoolTimeAnimationParameter_fullSpecialAttack, _coolTimeGaugeImage.fillAmount >= 1f);
        }

        void UpdateCoolTimeGaugeSpecialUnit(DeckUnitViewModel deckUnitViewModel)
        {
            // スペシャルユニットでは常にコストが見えるアイコンにする
            _coolTimeAnimator.SetBool(CoolTimeAnimationParameter_spUnit, true);
        }
        
        void PlaySoundEffectIfNeeded(DeckUnitViewModel prevViewModel, DeckUnitViewModel currentViewModel)
        {
            // 必殺ワザを使用できるようになったとき
            if (!CanUseSpecialAttack(prevViewModel) && CanUseSpecialAttack(currentViewModel))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_056);
                SoundEffectPlayer.Play(SoundEffectId.SSE_072_012);
            }
            
            // 召喚クールタイムが終わったとき
            if (!prevViewModel.IsSummoned && !prevViewModel.RemainingSummonCoolTime.IsZero() && 
                !currentViewModel.IsSummoned && currentViewModel.RemainingSummonCoolTime.IsZero())
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_037);
            }
        }

        public void OnButtonTapped()
        {
            if (CharacterId.IsEmpty() || _isSwipe)
            {
                return;
            }

            if (_deckUnitViewModel.RoleType != CharacterUnitRoleType.Special)
            {
                if (!CanSummon(_deckUnitViewModel) && !CanUseSpecialAttack(_deckUnitViewModel)) return;

                DoAsync.Invoke(this.GetCancellationTokenOnDestroy(), async cancellationToken =>
                {
                    // すぐにOnTapを処理してしまうと、
                    // ボタンタップアニメーションが再生されずに、召喚クールタイム待ちでDisableアニメーションになってしまうので
                    // タップアニメーションが再生されるように少し待つ
                    await UniTask.Delay(100, cancellationToken: cancellationToken);

                    TapAction tapAction = CanSummon(_deckUnitViewModel) ? TapAction.Summon : TapAction.SpecialAttack;

                    OnTapped?.Invoke(CharacterId, tapAction);
                });
            }
            else
            {
                // スペシャルのユニットは必殺技のみ可能とする
                if (!CanSpecialUnitUseSpecialAttack(_deckUnitViewModel)) return;

                TapAction tapAction = TapAction.SpecialUnitSpecialAttack;
                OnTapped?.Invoke(CharacterId, tapAction);
            }
        }

        public void PlayBattleEffect(UIEffectId uiEffectId)
        {
            _uiEffectManager?.Generate(uiEffectId, RectTransform)?.Play();
        }

        bool CanSummon(DeckUnitViewModel deckUnitViewModel)
        {
            return !deckUnitViewModel.IsEmpty()
                   && !deckUnitViewModel.IsSummoned
                   && deckUnitViewModel.RemainingSummonCoolTime.IsZero()
                   && !deckUnitViewModel.IsLackOfBattlePoint;
        }

        bool CanUseSpecialAttack(DeckUnitViewModel deckUnitViewModel)
        {
            return !deckUnitViewModel.IsEmpty()
                   && deckUnitViewModel.IsSummoned
                   && !deckUnitViewModel.IsSpecialAttackReady
                   && deckUnitViewModel.RemainingSpecialAttackCoolTime.IsZero();
        }

        bool CanSpecialUnitUseSpecialAttack(DeckUnitViewModel deckUnitViewModel)
        {
            return !deckUnitViewModel.IsEmpty()
                   && !deckUnitViewModel.IsSummoned
                   && !deckUnitViewModel.IsSpecialAttackReady
                   && !deckUnitViewModel.IsLackOfBattlePoint
                   && deckUnitViewModel.CanSummonAnySpecialUnit;
        }
        
        void CancelLongPress()
        {
            _longPress.Cancel();
        }
    }
}
