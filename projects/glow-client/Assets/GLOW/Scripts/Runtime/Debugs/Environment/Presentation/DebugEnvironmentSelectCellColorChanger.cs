using System;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.Environment.Presentation
{
    public class DebugEnvironmentSelectCellColorChanger : MonoBehaviour
    {
        [SerializeField] Text _environmentText;
        [SerializeField] Image _backgroundImage;

        const string DevelopKey = "dev";
        const string QaKey = "qa";
        const string StagingKey = "staging";
        const string ProductionKey = "prod";

        public void Start()
        {
            var envText = _environmentText.text;
            _backgroundImage.color = GetEnvironmentColor(envText);
        }

        public Color32 GetEnvironmentColor(string envText)
        {
            //dev-qaなど複数containsがある場合は、重要度順にif-else書いてる
            if(envText.Contains(ProductionKey, StringComparison.OrdinalIgnoreCase))
            {
                return Color.red;
            }
            else if(envText.Contains(StagingKey, StringComparison.OrdinalIgnoreCase))
            {
                return new Color32(255, 115, 0, 255);
            }
            else if(envText.Contains(QaKey, StringComparison.OrdinalIgnoreCase))
            {
                return new Color32(0, 200, 0, 255);
            }
            else if(envText.Contains(DevelopKey, StringComparison.OrdinalIgnoreCase))
            {
                return new Color32(0, 200, 230, 255);
            }
            else
            {
                //localやその他が入ってくる想定
                return Color.gray;
            }

        }
    }
}
