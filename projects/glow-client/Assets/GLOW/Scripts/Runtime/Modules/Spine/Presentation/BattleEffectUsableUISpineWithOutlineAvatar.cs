using System;
using System.Collections.Generic;
using UnityEngine;
using Spine.Unity;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using Object = UnityEngine.Object;

namespace GLOW.Modules.Spine.Presentation
{
    public class BattleEffectUsableUISpineWithOutlineAvatar : MonoBehaviour
    {
        //UnitImage.csと同一
        const string PhantomizedKeyword = "_PHANTOMIZED";
        static readonly int OutlineReferenceTexWidthPropertyId = Shader.PropertyToID("_OutlineReferenceTexWidth");

        [SerializeField] CanvasGroup _canvasGroup;
        [SerializeField] SkeletonGraphic _unitSkeleton;
        [SerializeField] SkeletonGraphic _unitOutlineSkeleton;
        [SerializeField] Material _straightAlphaUnitMaterial;
        [SerializeField] Material _unitOutlineMaterial;

        UnitImage _instancedInstancedUnitImage;
        Material _unitMaterialInstance;
        Material _unitOutlineMaterialInstance;
        List<UnitImage.Track> _tracks = new List<UnitImage.Track>();
        int _nextTrackId = 01;
        bool _isPhantomized;

        static Vector3 InstancedUnitImageScale => new Vector3(100, 100, 1);

        public UnitImage InstancedUnitImage => _instancedInstancedUnitImage;

        public Transform EffectRoot => _instancedInstancedUnitImage != null
            ? _instancedInstancedUnitImage.EffectRoot
            : null;

        public Vector3 EffectRootPosition => _instancedInstancedUnitImage != null
            ? _instancedInstancedUnitImage.EffectRoot.position
            : Vector3.zero;

        public CanvasGroup CanvasGroup
        {
            get => _canvasGroup;
            set => _canvasGroup = value;
        }

        public bool Flip
        {
            set
            {
                if (_unitSkeleton.Skeleton != null)
                {
                    _unitSkeleton.Skeleton.ScaleX = value ? -1f : 1f;
                }

                if (_unitOutlineSkeleton.Skeleton != null)
                {
                    _unitOutlineSkeleton.Skeleton.ScaleX = value ? -1f : 1f;
                }

                if (_instancedInstancedUnitImage != null)
                {
                    //EffectRoot向けにFlipする。UnitImageのSkeletonの状態は基本表示しない想定
                    _instancedInstancedUnitImage.Flip = value;
                }
            }
        }
        void Awake()
        {
            gameObject.SetActive(false);
        }

        void OnDestroy()
        {
            if (_unitMaterialInstance != null)
            {
                Destroy(_unitMaterialInstance);
                _unitMaterialInstance = null;
            }

            if (_unitOutlineMaterialInstance != null)
            {
                Destroy(_unitOutlineMaterialInstance);
                _unitOutlineMaterialInstance = null;
            }
        }

        public void Build(UnitImage unitImage)
        {
            if (unitImage == null)
            {
                gameObject.SetActive(false);
                return;
            }

            gameObject.SetActive(true);

            InitUnitImage(unitImage, this.transform );
            SetSpine(unitImage);
            SetOutlineSpine(unitImage);
        }

        void SetSpine(UnitImage unitImage)
        {
            var scale = unitImage.SkeletonScale;
            _unitSkeleton.skeletonDataAsset = unitImage.SkeletonAnimation.skeletonDataAsset;
            _unitSkeleton.transform.localScale = scale;
            _unitSkeleton.allowMultipleCanvasRenderers = true;

            _unitSkeleton.Initialize(true);
        }

        void InitUnitImage(UnitImage image, Transform parent)
        {
            if (null != _instancedInstancedUnitImage)
            {
                Destroy(_instancedInstancedUnitImage.gameObject);
            }

            //BattleEffectViewでエフェクトを生成するために必要。
            //表示はUIの方で対応するのでZ:1にしてGUIより後ろに隠してしまう

            _instancedInstancedUnitImage = Object.Instantiate(image).GetComponent<UnitImage>();
            _instancedInstancedUnitImage.transform.SetParent(parent, false);
            _instancedInstancedUnitImage.StartAnimation(CharacterUnitAnimation.Wait, CharacterUnitAnimation.Empty);
            var unitImageTransform = _instancedInstancedUnitImage.transform;
            unitImageTransform.localScale = InstancedUnitImageScale;

            //GUIより後ろに隠してしまう(effectの位置取得のために生かす)
            var localPosition = unitImageTransform.localPosition;
            localPosition = new Vector3(localPosition.x, localPosition.y, 1);
            unitImageTransform.localPosition = localPosition;
        }

        void SetOutlineSpine(UnitImage characterImage)
        {
            var scale = characterImage.SkeletonScale;

            _unitOutlineSkeleton.skeletonDataAsset = characterImage.SkeletonAnimation.skeletonDataAsset;
            _unitOutlineSkeleton.transform.localScale = scale;
            _unitOutlineSkeleton.allowMultipleCanvasRenderers = true;

            var tex = _unitSkeleton.mainTexture;
            var texWidth = tex != null ? tex.width : 1024f;

            // StraightAlphaInputにチェックがついているpngを使っていたとき、それに合わせたマテリアルを使う
            // アルファ別書き出し形式(乗算済アルファ)でも問題ないので、全部マテリアル適用
            if (_unitMaterialInstance != null)
            {
                Destroy(_unitMaterialInstance);
            }

            _unitMaterialInstance = new Material(_straightAlphaUnitMaterial);
            _unitSkeleton.material = _unitMaterialInstance;

            if (_isPhantomized)
            {
                _unitMaterialInstance.EnableKeyword(PhantomizedKeyword);
            }

            if (_unitOutlineMaterialInstance != null)
            {
                Destroy(_unitOutlineMaterialInstance);
            }

            _unitOutlineMaterialInstance = new Material(_unitOutlineMaterial);
            _unitOutlineSkeleton.material = _unitOutlineMaterialInstance;
            _unitOutlineSkeleton.material.SetFloat(OutlineReferenceTexWidthPropertyId, texWidth);

            _unitOutlineSkeleton.Initialize(true);
        }

        public void Animate(string animationName, bool isLoop = true)
        {
            if (_unitSkeleton.AnimationState == null || _unitOutlineSkeleton.AnimationState == null)
            {
                return;
            }

            if (!_unitSkeleton.Skeleton.Data.Animations.Exists(a => a.Name == animationName) ||
                !_unitOutlineSkeleton.Skeleton.Data.Animations.Exists(a => a.Name == animationName))
            {
                return;
            }

            _unitSkeleton.AnimationState.SetAnimation(0, animationName, isLoop);
            _unitOutlineSkeleton.AnimationState.SetAnimation(0, animationName, isLoop);
        }

        //unitImage.csのStartAnimationを参考にして書き換えている
        public void StartAnimation(
            CharacterUnitAnimation characterUnitAnimation,
            CharacterUnitAnimation nextAnimation,
            Action onCompleted = null)
        {
            if (_instancedInstancedUnitImage == null) return;

            //BattleEffectViewでエフェクトを生成するために必要。アニメーションによって位置が変化するため
            _instancedInstancedUnitImage.StartAnimation(characterUnitAnimation, nextAnimation, onCompleted);


            int id = _nextTrackId;
            _tracks.Add(new UnitImage.Track()
            {
                Id = _nextTrackId,
                NextAnimation = nextAnimation
            });

            _nextTrackId++;

            var trackEntry = _unitSkeleton.AnimationState.SetAnimation(
                0, characterUnitAnimation.Name, characterUnitAnimation.IsLoop);
            _unitOutlineSkeleton.AnimationState.SetAnimation(
                0, characterUnitAnimation.Name, characterUnitAnimation.IsLoop);

            if (!characterUnitAnimation.IsLoop)
            {
                trackEntry.Complete += _ =>
                {
                    onCompleted?.Invoke();

                    UnitImage.Track track = _tracks.Find(track => track.Id == id);

                    if (_tracks[_tracks.Count - 1].Id == track.Id &&
                        track.NextAnimation != CharacterUnitAnimation.Empty)
                    {
                        StartAnimation(track.NextAnimation, CharacterUnitAnimation.Empty);
                    }

                    _tracks.Remove(track);
                };
            }
        }

        public float GetAnimationDuration(CharacterUnitAnimation animation)
        {
            if (_unitOutlineSkeleton.AnimationState == null) return 0f;

            var skeletonAnimation = _unitOutlineSkeleton.AnimationState.Data.SkeletonData.FindAnimation(animation.Name);
            return skeletonAnimation?.Duration ?? 0f;
        }

        public string GetCurrentAnimationStateName()
        {
            if (_unitSkeleton.AnimationState == null) return "";

            return _unitSkeleton.AnimationState.GetCurrent(0)?.Animation.Name;
        }

        public void SetPhantomized(bool isPhantomized)
        {
            if (_isPhantomized == isPhantomized) return;

            _isPhantomized = isPhantomized;

            // シェーダーキーワードの設定
            if (_unitMaterialInstance != null)
            {
                if (_isPhantomized)
                {
                    _unitMaterialInstance.EnableKeyword(PhantomizedKeyword);
                }
                else
                {
                    _unitMaterialInstance.DisableKeyword(PhantomizedKeyword);
                }
            }
        }

        public void SetEffectMaskSetting(SpriteMaskInteraction maskInteraction)
        {
            if (_instancedInstancedUnitImage == null) return;

            var spriteRenderers = _instancedInstancedUnitImage.GetComponentsInChildren<SpriteRenderer>();
            foreach (var spriteRenderer in spriteRenderers)
            {
                spriteRenderer.maskInteraction = maskInteraction;
            }
        }
    }
}
