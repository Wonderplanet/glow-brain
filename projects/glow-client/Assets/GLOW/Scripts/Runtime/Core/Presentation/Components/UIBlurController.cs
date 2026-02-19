using UnityEngine;
using UnityEngine.UI;
using UnityEditor;

[ExecuteInEditMode]
public class UIBlurController : MonoBehaviour
{
    [Range(0, 100)]
    public float blurRadius = 10.0f;

    private static readonly int BlurRadiusID = Shader.PropertyToID("_BlurRadius");
    private Image _image;
    private Material _instanceMaterial;

    void Awake()
    {
        _image = GetComponent<Image>();
        CacheMaterial();
        ApplyBlurRadius();
    }

    void OnEnable()
    {
        if (_image == null)
            _image = GetComponent<Image>();

        CacheMaterial();
        ApplyBlurRadius();
    }

    void OnValidate()
    {
        ApplyBlurRadius();
    }

    void Update()
    {
        ApplyBlurRadius();
    }

    private void CacheMaterial()
    {
        if (_image == null)
            return;

        _instanceMaterial = _image.material;
    }

    public void ApplyBlurRadius()
    {
        if (_instanceMaterial == null)
            return;

        _instanceMaterial.SetFloat(BlurRadiusID, blurRadius);
        _image.SetMaterialDirty();
    }
}

#if UNITY_EDITOR
[CustomEditor(typeof(UIBlurController))]
public class UIBlurControllerEditor : Editor
{
    public override void OnInspectorGUI()
    {
        UIBlurController controller = (UIBlurController)target;

        EditorGUI.BeginChangeCheck();
        float newBlurRadius = EditorGUILayout.Slider("Blur Radius", controller.blurRadius, 0, 100);
        if (EditorGUI.EndChangeCheck())
        {
            Undo.RecordObject(controller, "Change Blur Radius");
            controller.blurRadius = newBlurRadius;
            controller.ApplyBlurRadius();
        }
    }
}
#endif
