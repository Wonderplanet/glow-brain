using System.Collections;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using Spine.Unity;
using WonderPlanet.ResourceManagement.Spine;

namespace GLOW.Modules.Spine.Presentation
{
    public class UISpineWithOutlineAvatar : UIObject
    {
        //UnitImage.csと同一
        const string PhantomizedKeyword = "_PHANTOMIZED";
        static readonly int OutlineReferenceTexWidthPropertyId = Shader.PropertyToID("_OutlineReferenceTexWidth");

        [SerializeField] CanvasGroup _canvasGroup;
        [SerializeField] SkeletonGraphic _unitSkeleton;
        [SerializeField] SkeletonGraphic _unitOutlineSkeleton;
        [SerializeField] Material _straightAlphaUnitMaterial;
        [SerializeField] Material _unitOutlineMaterial;
        [SerializeField] bool _useRenderTexture = false;

        bool RenderTextureEnable
        {
            // REFACTOR: オブジェクトの階層を移動したりするとエラーになるので、改修の余地あり
            set
            {
                var inCanvas = GetComponentInParent<Canvas>() != null;
                if (!inCanvas)
                {
                    Debug.LogWarning(nameof(UISpineWithOutlineAvatar) +
                                     ": Error:Canvas以下に配置されてないない為、SkeletonGraphicRenderTextureを有効化できません");
                    return;
                }


                //順番依存1. アウトライン(Hierarchyの関係で、先にアウトラインを生成する)
                if (value && _skeletonGraphicOutlineRenderTexture == null)
                {
                    _skeletonGraphicOutlineRenderTexture = _unitOutlineSkeleton.gameObject
                        .AddComponent<SkeletonGraphicRenderTexture>();

                    //quadMaterialをoutline向けに変更
                    _skeletonGraphicOutlineRenderTexture.quadMaterial = _unitOutlineMaterial;
                }

                if (_skeletonGraphicOutlineRenderTexture != null)
                {
                    if (value)
                    {
                        _skeletonGraphicOutlineRenderTexture.ResetMeshRendererMaterials();
                    }

                    _skeletonGraphicOutlineRenderTexture.enabled = value;
                }

                //順番依存2. 通常
                if (value && _skeletonGraphicRenderTexture == null)
                {
                    _skeletonGraphicRenderTexture =
                        _unitSkeleton.gameObject.AddComponent<SkeletonGraphicRenderTexture>();
                    //quadMaterialを通常向けに変更
                    _skeletonGraphicRenderTexture.quadMaterial = _straightAlphaUnitMaterial;
                }

                if (_skeletonGraphicRenderTexture != null)
                {
                    if (value)
                    {
                        _skeletonGraphicRenderTexture.ResetMeshRendererMaterials();
                    }

                    _skeletonGraphicRenderTexture.enabled = value;
                }

            }
        }

        Material _material;
        Material _outlineMaterial;
        SkeletonGraphicRenderTexture _skeletonGraphicRenderTexture;
        SkeletonGraphicRenderTexture _skeletonGraphicOutlineRenderTexture;
        bool _isPhantomized;

        public CanvasGroup CanvasGroup
        {
            get => _canvasGroup;
            set => _canvasGroup = value;
        }

        public bool Flip
        {
            set
            {
                _unitSkeleton.Skeleton.ScaleX = value ? -1f : 1f;
                _unitOutlineSkeleton.Skeleton.ScaleX = value ? -1f : 1f;
            }
        }

        protected override void Awake()
        {
            base.Awake();

            gameObject.SetActive(false);
        }

        protected override void OnDestroy()
        {
            if (_material != null)
            {
                Destroy(_material);
                _material = null;
            }

            if (_outlineMaterial != null)
            {
                Destroy(_outlineMaterial);
                _outlineMaterial = null;
            }

            base.OnDestroy();
        }

        public void SetAvatarScale(Vector3 scale)
        {
            _unitSkeleton.transform.localScale = scale;
            _unitOutlineSkeleton.transform.localScale = scale;
        }

        public void SetSkeleton(SkeletonDataAsset skeleton)
        {
            _unitSkeleton.gameObject.SetActive(true);
            _unitOutlineSkeleton.gameObject.SetActive(true);
            Build(skeleton);
        }

        void Build(SkeletonDataAsset skeleton)
        {
            gameObject.SetActive(true);
            SetSpine(skeleton);
            SetOutlineSpine(skeleton);

            RenderTextureEnable = _useRenderTexture;
        }

        public string GetCurrentAnimationStateName()
        {
            return _unitSkeleton.AnimationState.GetCurrent(0)?.Animation.Name;
        }

        void SetSpine(SkeletonDataAsset skeleton)
        {
            _unitSkeleton.skeletonDataAsset = skeleton;
            _unitSkeleton.allowMultipleCanvasRenderers = true;

            _unitSkeleton.Initialize(true);
            _unitSkeleton.UpdateMesh();
        }

        void SetOutlineSpine(SkeletonDataAsset skeleton)
        {
            _unitOutlineSkeleton.skeletonDataAsset = skeleton;
            _unitOutlineSkeleton.allowMultipleCanvasRenderers = true;

            var tex = _unitSkeleton.mainTexture;
            var texWidth = tex != null ? tex.width : 1024f;

            // StraightAlphaInputにチェックがついているpngを使っていたとき、それに合わせたマテリアルを使う
            // アルファ別書き出し形式(乗算済アルファ)でも問題ないので、全部マテリアル適用
            if (_material != null)
            {
                Destroy(_material);
            }
            
            _material = new Material(_straightAlphaUnitMaterial);
            _unitSkeleton.material = _material;

            if (_isPhantomized)
            {
                _material.EnableKeyword(PhantomizedKeyword);
            }

            // NOTE: 現行spineはマテリアルにStraightAlphaInputにチェックがついていないと白い枠のようなゴミが表示される
            // そのため_unitOutlineMaterialは、マテリアル本体にStraightAlphaInputにチェックがついている
            if (_outlineMaterial != null)
            {
                Destroy(_outlineMaterial);
            }
            
            _outlineMaterial = new Material(_unitOutlineMaterial);
            _unitOutlineSkeleton.material = _outlineMaterial;
            _unitOutlineSkeleton.material.SetFloat(OutlineReferenceTexWidthPropertyId, texWidth);

            _unitOutlineSkeleton.Initialize(true);
            _unitOutlineSkeleton.UpdateMesh();
        }

        public bool IsFindAnimation(string animationName)
        {
            return _unitSkeleton.Skeleton.Data.Animations.Exists(a => a.Name == animationName);
        }

        public void Animate(string animationName, bool isLoop = true)
        {
            if (!_unitSkeleton.Skeleton.Data.Animations.Exists(a => a.Name == animationName)) return;
            _unitSkeleton.AnimationState.SetAnimation(0, animationName, isLoop);

            if (!_unitOutlineSkeleton.Skeleton.Data.Animations.Exists(a => a.Name == animationName)) return;
            _unitOutlineSkeleton.AnimationState.SetAnimation(0, animationName, isLoop);
        }

        public void Animate(string animationName, string nextAnimationName, bool isNextAnimationLoop = true)
        {
            StartCoroutine(Animates(animationName, nextAnimationName, isNextAnimationLoop));
        }

        IEnumerator Animates(string animationName, string nextAnimationName, bool isNextAnimationLoop)
        {
            var skeletonAnimation = _unitSkeleton.Skeleton.Data.Animations.Find(a => a.Name == animationName);
            if (skeletonAnimation == null) yield break;
            _unitSkeleton.AnimationState.SetAnimation(0, animationName, false);
            _unitOutlineSkeleton.AnimationState.SetAnimation(0, animationName, false);
            yield return new WaitForSeconds(skeletonAnimation.Duration);
            Animate(nextAnimationName, isNextAnimationLoop);
        }

        public void SetRaycastTarget(bool raycast)
        {
            _unitSkeleton.raycastTarget = raycast;
            _unitOutlineSkeleton.raycastTarget = raycast;
        }

        public void SetPhantomized(bool isPhantomized)
        {
            if (_isPhantomized == isPhantomized) return;

            _isPhantomized = isPhantomized;

            // シェーダーキーワードの設定
            if (_material != null)
            {
                if (_isPhantomized)
                {
                    _material.EnableKeyword(PhantomizedKeyword);
                }
                else
                {
                    _material.DisableKeyword(PhantomizedKeyword);
                }
            }
        }
    }
}