using System;
using UnityEngine;
using UnityEngine.UI;

#if UNITY_EDITOR
using UnityEditor;
#endif

namespace GLOW.Scenes.GachaAnim.Presentation.Views.Shader
{
    [ExecuteAlways] // エディターモードでも Update() を実行
    public class ColorMappingWithScrollShaderController : MonoBehaviour
    {
        // Image または RawImage の参照から取得するマテリアル（固有インスタンス）
        Material _material;

        // シェーダープロパティId
        static readonly int BrightColorPropertyId = UnityEngine.Shader.PropertyToID("_BrightColor");
        static readonly int DarkColorPropertyId = UnityEngine.Shader.PropertyToID("_DarkColor");
        static readonly int ContrastPropertyId = UnityEngine.Shader.PropertyToID("_Contrast");
        static readonly int SpeedUPropertyId = UnityEngine.Shader.PropertyToID("_ScrollSpeedU");
        static readonly int SpeedVPropertyId = UnityEngine.Shader.PropertyToID("_ScrollSpeedV");

        // インスペクターから変更するためのフィールド
        [SerializeField] Color _brightColor = Color.red;   // 明るい色
        [SerializeField] Color _darkColor = Color.blue;    // 暗い色
        [SerializeField, Range(0.0f, 20.0f)] float _contrast = 1.0f; // コントラスト
        [SerializeField] float _speedU; // Scroll Speed U
        [SerializeField] float _speedV; // Scroll Speed V

        // 前回の値を保持して監視するための変数
        Color _previousBrightColor;
        Color _previousDarkColor;
        float _previousContrast;
        float _previousSpeedU;
        float _previousSpeedV;

        bool _isExistingMaterialUsed;

        // 対象のシェーダー名（使用するシェーダー）
        const string TargetShaderName = "Custom/ColorMappingWithScrollShader";

        /// <summary>
        /// スクリプトが有効になったタイミングで、正しいシェーダーを使ったマテリアルをセットします。
        /// </summary>
        void OnEnable()
        {
            EnsureCorrectMaterial();
            ApplyChanges();

            // 初期値を記録
            _previousBrightColor = _brightColor;
            _previousDarkColor = _darkColor;
            _previousContrast = _contrast;
            _previousSpeedU = _speedU;
            _previousSpeedV = _speedV;
        }

        /// <summary>
        /// 現在セットされているマテリアルが対象シェーダーを使用していなければ、
        /// 新しく対象シェーダーを使ったマテリアルインスタンスを作成してセットします。
        /// </summary>
        void EnsureCorrectMaterial()
        {
            // すでにmaterialが設定されていたら何もしない
            if(_material != null) return;

            // Image または RawImage コンポーネントを取得
            Image img = GetComponent<Image>();
            RawImage rawImg = GetComponent<RawImage>();
            Material currentMaterial;

            if (img != null)
            {
                currentMaterial = img.material;
            }
            else if (rawImg != null)
            {
                currentMaterial = rawImg.material;
            }
            else
            {
                Debug.LogError("Image または RawImage コンポーネントが見つかりません。");
                return;
            }

            // すでに設定されているマテリアルが存在し、
            // そのシェーダーが対象のものであればそれをそのまま利用
            if (currentMaterial != null &&
                currentMaterial.shader != null &&
                currentMaterial.shader.name == TargetShaderName)
            {
                _material = currentMaterial;
                _isExistingMaterialUsed = true;
                return;
            }

            // 対象のシェーダーを取得
            UnityEngine.Shader targetShader = UnityEngine.Shader.Find(TargetShaderName);
            if (targetShader == null)
            {
                Debug.LogError("ターゲットのシェーダーが見つかりません: " + TargetShaderName);
                return;
            }

            // 対象シェーダーを使った新しいマテリアルインスタンスを生成してセット
            _material = new Material(targetShader);

            if (img != null)
            {
                img.material = _material;
            }
            else if (rawImg != null)
            {
                rawImg.material = _material;
            }
        }

        /// <summary>
        /// マテリアルに各種プロパティを適用します
        /// </summary>
        void ApplyChanges()
        {
            if (_material == null) return;
            _material.SetColor(BrightColorPropertyId, _brightColor);
            _material.SetColor(DarkColorPropertyId, _darkColor);
            _material.SetFloat(ContrastPropertyId, _contrast);
            _material.SetFloat(SpeedUPropertyId, _speedU);
            _material.SetFloat(SpeedVPropertyId, _speedV);
        }

        void ClearCacheMaterial()
        {
            if (_material == null) return;

            ClearAttachedMaterial();
            DestroyCacheMaterial();

            _isExistingMaterialUsed = false;
        }

        void ClearAttachedMaterial()
        {
            var img = GetComponent<Image>();
            var rawImg = GetComponent<RawImage>();
            if (img != null && !_isExistingMaterialUsed)
            {
                img.material = null;
            }
            else if (rawImg != null && !_isExistingMaterialUsed)
            {
                rawImg.material = null;
            }
        }

        void DestroyCacheMaterial()
        {
            if (Application.isPlaying)
            {
                Destroy(_material);
            }
            // アセットに登録されているmaterialを利用しているときDestroyすると権限エラーを起こす(DestroyImmediateするとアセット削除される)。
            // アセット登録material利用時はedit modeでDestroy系処理が呼ばれないので、メモリリークしている可能性あり。
            // しかし良い解決案が無いため一度許容とする
            else if(!_isExistingMaterialUsed)
            {
                // edit modeではDestroy行うとエラー出るので、DestroyImmediateを使う
                DestroyImmediate(_material,false);
            }

            _material = null;
        }

        /// <summary>
        /// ゲーム中やアニメーションからのパラメータ変更を監視してリアルタイムに反映します
        /// </summary>
        void Update()
        {
            if (_brightColor != _previousBrightColor ||
                _darkColor != _previousDarkColor ||
                _contrast != _previousContrast ||
                _speedU != _previousSpeedU ||
                _speedV != _previousSpeedV)
            {
                ApplyChanges();

                // 変更後の値を記録
                _previousBrightColor = _brightColor;
                _previousDarkColor = _darkColor;
                _previousContrast = _contrast;
                _previousSpeedU = _speedU;
                _previousSpeedV = _speedV;
            }
        }

        void OnDestroy()
        {
            ClearCacheMaterial();
        }

#if UNITY_EDITOR
        /// <summary>
        /// エディタ上でスクリプトがセットされたタイミングにも実行（Reset時など）
        /// </summary>
        void Reset()
        {
            EnsureCorrectMaterial();
        }

        /// <summary>
        /// エディタ上でインスペクターの値変更をリアルタイムに反映させます
        /// </summary>
        void OnValidate()
        {
            if (Application.isPlaying) return;

            EnsureCorrectMaterial();
            ApplyChanges();

            // シーンビュー更新
            EditorApplication.QueuePlayerLoopUpdate();
            SceneView.RepaintAll();
        }
#endif
    }
}
