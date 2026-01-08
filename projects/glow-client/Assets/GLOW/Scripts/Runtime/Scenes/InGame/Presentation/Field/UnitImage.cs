using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using Spine;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class UnitImage : MonoBehaviour
    {
        public class Track
        {
            public int Id;
            public CharacterUnitAnimation NextAnimation;
        }

        const string PhantomizedKeyword = "_PHANTOMIZED";

        //BattleEffectUsableUISpineWithOutlineAvatar.csに重複記述あり
        static readonly int UnitColorOutlineColorPropertyId = Shader.PropertyToID("_UnitColor_OutlineColor");

        static readonly int ShowUnitColorPropertyId = Shader.PropertyToID("_UseUnitColor");
        static readonly int PhantomizedPropertyId = Shader.PropertyToID("_Phantomized");
        static readonly Color UnitColorDefault = new Color(1f, 1f, 1f, 1f);
        static readonly Color UnitColorLess = new Color(0.8f, 0.8f, 0.8f, 1f);
        static readonly Color UnitColorRed = new Color(1f, 0f, 0f, 1f);
        static readonly Color UnitColorBlue = new Color(0f, 0f, 1f, 1f);
        static readonly Color UnitColorYellow = new Color(1f, 1f, 0f, 1f);
        static readonly Color UnitColorGreen = new Color(0f, 1f, 0f, 1f);

        [SerializeField] SkeletonAnimation _skeletonAnimation;
        [SerializeField] MeshRenderer _meshRenderer;
        [SerializeField] MeshRenderer _outlineMeshRenderer;
        [SerializeField] Transform _tagPosition;
        [SerializeField] Transform _effectRoot;
        [SerializeField] List<Animator> _constantlyEffectAnimators;
        [SerializeField] float _unitSize = 1f;

        readonly MultipleSwitchController _pauseController = new ();
        Material _material;
        MaterialPropertyBlock _outlineMaterialPropertyBlock;
        float _unitHeight = 1f;

        Color _unitColor = UnitColorDefault;
        bool _isPhantomized;
        CharacterUnitAnimation _currentAnimation = CharacterUnitAnimation.Empty;
        CharacterUnitAnimation _nextAnimation = CharacterUnitAnimation.Empty;
        List<Track> _tracks = new List<Track>();
        int _nextTrackId = 01;
        Tweener _shakeTweener;

        public Transform TagPosition => _tagPosition;
        public Transform EffectRoot => _effectRoot;
        public CharacterUnitAnimation CurrentAnimation => _currentAnimation;
        public CharacterUnitAnimation NextAnimation => _nextAnimation;
        public SkeletonAnimation SkeletonAnimation => _skeletonAnimation;
        public MeshRenderer MeshRenderer => _meshRenderer;
        public Vector3 SkeletonScale => _skeletonAnimation.transform.localScale;
        public Bone RootBone => _skeletonAnimation.Skeleton.FindBone("root");
        public Bone SpineBone => _skeletonAnimation.Skeleton.FindBone("spine");
        public float UnitHeight => _unitHeight;

        public bool Flip
        {
            set
            {
                _skeletonAnimation.Skeleton.ScaleX = value ? -1f : 1f;
                _effectRoot.localScale = new Vector3(value ? -1f : 1f, 1f, 1f);
            }
        }

        public Color Color
        {
            get => _skeletonAnimation.Skeleton.GetColor();
            set => _skeletonAnimation.Skeleton.SetColor(value);
        }

        public int SortingOrder
        {
            get => _meshRenderer.sortingOrder;
            set => _meshRenderer.sortingOrder = value;
        }

        public CharacterUnitAnimation GetMirrorAnimation(UnitAnimationType animationType)
        {
            switch (animationType)
            {
                case UnitAnimationType.Empty:
                    return CharacterUnitAnimation.Empty;
                case UnitAnimationType.Wait:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorWait.Name) != null
                        ? CharacterUnitAnimation.MirrorWait
                        : CharacterUnitAnimation.Wait;
                case UnitAnimationType.WaitJoy:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorWaitJoy.Name) != null
                        ? CharacterUnitAnimation.MirrorWaitJoy
                        : CharacterUnitAnimation.WaitJoy;
                case UnitAnimationType.Move:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorMove.Name) != null
                        ? CharacterUnitAnimation.MirrorMove
                        : CharacterUnitAnimation.Move;
                case UnitAnimationType.Attack:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorAttack.Name) != null
                        ? CharacterUnitAnimation.MirrorAttack
                        : CharacterUnitAnimation.Attack;
                case UnitAnimationType.SpecialAttackCharge:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorSpecialAttackCharge.Name) != null
                        ? CharacterUnitAnimation.MirrorSpecialAttackCharge
                        : CharacterUnitAnimation.SpecialAttackCharge;
                case UnitAnimationType.SpecialAttack:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorSpecialAttack.Name) != null
                        ? CharacterUnitAnimation.MirrorSpecialAttack
                        : CharacterUnitAnimation.SpecialAttack;
                case UnitAnimationType.Damage:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorDamage.Name) != null
                        ? CharacterUnitAnimation.MirrorDamage
                        : CharacterUnitAnimation.Damage;
                case UnitAnimationType.KnockBack:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorKnockBack.Name) != null
                        ? CharacterUnitAnimation.MirrorKnockBack
                        : CharacterUnitAnimation.KnockBack;
                case UnitAnimationType.Death:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorDeath.Name) != null
                        ? CharacterUnitAnimation.MirrorDeath
                        : CharacterUnitAnimation.Death;
                case UnitAnimationType.Escape:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorEscape.Name) != null
                        ? CharacterUnitAnimation.MirrorEscape
                        : CharacterUnitAnimation.Escape;
                case UnitAnimationType.Appearing:
                    return CharacterUnitAnimation.Appearing;
                case UnitAnimationType.Stun:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorStun.Name) != null
                        ? CharacterUnitAnimation.MirrorStun
                        : CharacterUnitAnimation.Stun;
                case UnitAnimationType.Freeze:
                    return _skeletonAnimation.state.Data.SkeletonData.FindAnimation(CharacterUnitAnimation.MirrorFreeze.Name) != null
                        ? CharacterUnitAnimation.MirrorFreeze
                        : CharacterUnitAnimation.Freeze;
                case UnitAnimationType.SpecialAttackCutIn:
                    return CharacterUnitAnimation.SpecialAttackCutIn;
                default:
                    return CharacterUnitAnimation.Empty;
            }
        }

        void Awake()
        {
            if (_outlineMeshRenderer != null)
            {
                _outlineMaterialPropertyBlock = new MaterialPropertyBlock();
            }

            _pauseController.OnStateChanged = OnPause;
        }

        void OnDestroy()
        {
            _pauseController.Dispose();

            if (_material != null)
            {
                Destroy(_material);
                _material = null;
            }
        }

        void Start()
        {
            foreach (var animator in _constantlyEffectAnimators)
            {
                var boneFollower = animator.GetComponent<SpineBoneFollower>();
                if (boneFollower != null)
                {
                    boneFollower.Initialize();
                }
            }

            CalculateUnitHeight();
        }

        void Update()
        {
            if (_outlineMeshRenderer != null && _outlineMaterialPropertyBlock != null)
            {
                _outlineMeshRenderer.GetPropertyBlock(_outlineMaterialPropertyBlock);
                _outlineMaterialPropertyBlock.SetColor(UnitColorOutlineColorPropertyId, _unitColor);
                _outlineMaterialPropertyBlock.SetFloat(ShowUnitColorPropertyId, _unitColor == UnitColorLess ? 0f : 1f);
                _outlineMeshRenderer.SetPropertyBlock(_outlineMaterialPropertyBlock);
            }
        }

        public void SetUnitColor(CharacterColor color)
        {
            _unitColor = color switch
            {
                CharacterColor.Red => UnitColorRed,
                CharacterColor.Blue => UnitColorBlue,
                CharacterColor.Yellow => UnitColorYellow,
                CharacterColor.Green => UnitColorGreen,
                CharacterColor.Colorless => UnitColorLess,
                _ => UnitColorDefault
            };
        }

        public void SetPhantomized(bool isPhantomized)
        {
            if (_isPhantomized == isPhantomized) return;

            _isPhantomized = isPhantomized;

            if (_material == null)
            {
                var original = _skeletonAnimation.SkeletonDataAsset.atlasAssets[0].PrimaryMaterial;
                if (original != null)
                {
                    _material = new Material(original);
                    _skeletonAnimation.CustomMaterialOverride[original] = _material;
                }
            }
            if (_material == null) return;

            if (_isPhantomized)
            {
                _material.EnableKeyword(PhantomizedKeyword);
            }
            else
            {
                _material.DisableKeyword(PhantomizedKeyword);
            }
        }

        public async UniTask PlayAnimation(
            CharacterUnitAnimation characterUnitAnimation,
            CharacterUnitAnimation nextAnimation,
            CancellationToken cancellationToken)
        {
            bool isCompleted = false;

            StartAnimation(characterUnitAnimation, nextAnimation, () => isCompleted = true);

            await UniTask.WaitUntil(() => isCompleted, cancellationToken: cancellationToken);
        }

        public TrackEntry StartAnimation(
            CharacterUnitAnimation characterUnitAnimation,
            CharacterUnitAnimation nextAnimation,
            Action onCompleted = null)
        {
            _currentAnimation = characterUnitAnimation;
            _nextAnimation = nextAnimation;

            int id = _nextTrackId;
            _tracks.Add(new Track()
            {
                Id = _nextTrackId,
                NextAnimation = nextAnimation
            });

            _nextTrackId++;

            var trackEntry = _skeletonAnimation.AnimationState.SetAnimation(
                0, characterUnitAnimation.Name, characterUnitAnimation.IsLoop);

            if (characterUnitAnimation.IsHoldAtEnd)
            {
                trackEntry.TrackTime = trackEntry.AnimationEnd;
                trackEntry.TimeScale = 0f;
            }

            foreach (var animator in _constantlyEffectAnimators)
            {
                animator.SetTrigger(characterUnitAnimation.AnimatorHash);
            }

            if (!characterUnitAnimation.IsLoop && !characterUnitAnimation.IsHoldAtEnd)
            {
                trackEntry.Complete += _ =>
                {
                    onCompleted?.Invoke();

                    Track track = _tracks.Find(track => track.Id == id);

                    if (_tracks[_tracks.Count - 1].Id == track.Id && track.NextAnimation != CharacterUnitAnimation.Empty)
                    {
                        StartAnimation(track.NextAnimation, CharacterUnitAnimation.Empty);
                    }

                    _tracks.Remove(track);
                };
            }

            return trackEntry;
        }

        public void SetNextAnimation(CharacterUnitAnimation nextAnimation)
        {
            if (_tracks.Count > 0)
            {
                Track track = _tracks[_tracks.Count - 1];
                track.NextAnimation = nextAnimation;

                _nextAnimation = nextAnimation;
            }
        }

        public void Shake(float duration, float strength, int vibrato)
        {
            _shakeTweener?.Kill(true);

            _shakeTweener = transform.DOShakePosition(duration, strength, vibrato, 90f, false, false);
            _shakeTweener.onComplete = () => _shakeTweener = null;
            _shakeTweener.SetLink(gameObject);
        }

        public MultipleSwitchHandler PauseAnimation(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }

        public MultipleSwitchHandler PauseAnimation()
        {
            return _pauseController.TurnOn();
        }

        public void ApplyUnitSizeToSpecifiedBattleEffect(BaseBattleEffectView effect)
        {
            if (effect == null) return;
            effect.gameObject.transform.localScale *= _unitSize;
        }

        public void SetConstantlyEffectAnimatorsEnabled(bool isEnabled)
        {
            foreach (var animator in _constantlyEffectAnimators)
            {
                animator.gameObject.SetActive(isEnabled);
            }
        }

        void OnPause(bool isPause)
        {
            _skeletonAnimation.AnimationState.TimeScale = isPause ? 0f : 1f;

            foreach (var animator in _constantlyEffectAnimators)
            {
                animator.speed = isPause ? 0f : 1f;
            }
        }

        void CalculateUnitHeight()
        {
            if (_meshRenderer != null)
            {
                var bounds = _meshRenderer.bounds;
                _unitHeight = bounds.size.y;
            }
        }
    }
}
