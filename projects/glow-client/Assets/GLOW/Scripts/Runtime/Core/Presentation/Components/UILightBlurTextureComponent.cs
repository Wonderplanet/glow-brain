using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    [ExecuteInEditMode]
    [RequireComponent(typeof(RawImage))]
    public class UILightBlurTextureComponent : UIObject
    {
        [Header("基本設定")]
        [SerializeField] RawImage _targetImage; 
        [SerializeField] Material _blurMaterial;

        [Header("ブラー設定")]
        [Range(0.1f, 1f)]
        [SerializeField] float _resolutionScale = 0.5f;
        [Range(0.1f, 4f)]
        [SerializeField] float _blurStrength = 0.5f;
        [Range(0.01f, 1f)]
        [SerializeField] float _blurCoef = 0.1f;
        [Range(1, 4)]
        [SerializeField] int iteration = 2;
        [Header("更新処理で実行するか？(静止画ならfalse)")]
        [SerializeField] bool _isUpdateBlur = false;
        [Header("エディタでパラメータ更新した時に反映されるようにするか？")]
        [SerializeField] bool _autoUpdateInEditor = true;
        [Header("確認用テクスチャ")]
        [SerializeField] Texture _baseTexture;
        
        Texture _sourceTexture;
        RenderTexture _renderTextureA;
        RenderTexture _renderTextureB;
        float[] _blurWeights = new float[7];

        public void SetTexture(Texture texture)
        {
            _sourceTexture = texture;
            ExecuteBlur();
        }
        
        protected override void OnEnable()
        {
            if (Application.isPlaying || _autoUpdateInEditor)
            {
                if (_baseTexture == null) return;
                
                SetTexture(_baseTexture);
                ExecuteBlur();
            }
        }

#if UNITY_EDITOR
        // Editorでも即時反映させたいときに使う
        protected override void OnValidate()
        {
            if (!Application.isPlaying && _autoUpdateInEditor)
            {
                if (_baseTexture == null) return;
                
                SetTexture(_baseTexture);
                ExecuteBlur();
            }
        }
#endif
        
        protected override void OnDestroy()
        {
            ReleaseRenderTextures();
        }

        void Update()
        {
            // アニメーションの場合は_isUpdateBlurをtrueにする
            // 静止画の場合はrをfalseにする
            if (Application.isPlaying && _isUpdateBlur)
            {
                ExecuteBlur();
            }
        }

        void ExecuteBlur()
        {
            if (_sourceTexture == null || _targetImage == null) return;
            
            CalculateWeights();
            
            _blurMaterial.SetFloat("_BlurStrength", _blurStrength);
            _blurMaterial.SetFloatArray("_BlurWeights", _blurWeights);
            int srcWidth = _sourceTexture.width;
            int srcHeight = _sourceTexture.height;
            
            // 元の画像サイズに解像度のスケール値をかけたものを設定する(スケール値が1の時は元画像のサイズのまま)
            int width = Mathf.Max(1, Mathf.RoundToInt(srcWidth * _resolutionScale));
            int height = Mathf.Max(1, Mathf.RoundToInt(srcHeight * _resolutionScale));
            
            EnsureRTs(width, height);

            var sourceTex = _sourceTexture;
            for (int i = 0; i < iteration; i++)
            {
                Graphics.Blit(sourceTex, _renderTextureA, _blurMaterial, 0); 
                Graphics.Blit(_renderTextureA, _renderTextureB, _blurMaterial, 1);
                sourceTex = _renderTextureB;
            }

            _targetImage.texture = sourceTex;
        }

        void EnsureRTs(int width, int height)
        {
            if (_renderTextureA == null || _renderTextureA.width != width || _renderTextureA.height != height)
            {
                ReleaseRenderTextures();
                _renderTextureA = new RenderTexture(width, height, 0, RenderTextureFormat.ARGB32);
                _renderTextureB = new RenderTexture(width, height, 0, RenderTextureFormat.ARGB32);
                _renderTextureA.name = "BlurTempA";
                _renderTextureB.name = "BlurTempB";
            }
        }

        void ReleaseRenderTextures()
        {
            if (_renderTextureA != null)
            {
                _renderTextureA.Release();
                DestroyImmediate(_renderTextureA);
            }
            if (_renderTextureB != null)
            {
                _renderTextureB.Release();
                DestroyImmediate(_renderTextureB);
            }
            _renderTextureA = null;
            _renderTextureB = null;
        }
        
        void CalculateWeights()
        {
            float total = 0;
            float d = _blurCoef * _blurCoef;
            
            for (int x = -3; x <= 3; x++)
            {
                float w = (1f / Mathf.Sqrt(2 * Mathf.PI * d)) * Mathf.Exp(-1 * (x * x) / (2f * d));
                _blurWeights[x + 3] = w;
                total += w;
            }
            
            for (int i = 0; i < _blurWeights.Length; i++)
            {
                _blurWeights[i] /= total;
            }
        }
    }
}