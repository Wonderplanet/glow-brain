using System.Linq;
using GLOW.Core.Presentation.Components;
using TMPro;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class VerticalText : UIText
    {
        static readonly char[] RotationCharacters =
        {
            'ー',
            '…',
            '〜',
        };

        void Update()
        {
            RotateSpecificCharacters();
        }

        void RotateSpecificCharacters()
        {
            TextMeshPro.ForceMeshUpdate();

            var textInfo = TextMeshPro.textInfo;
            if (textInfo.characterCount == 0)
            {
                return;
            }

            for (int index = 0; index < textInfo.characterCount; index++)
            {
                var charaInfo = textInfo.characterInfo[index];
                if (!charaInfo.isVisible) continue;
                if (!RotationCharacters.Contains(charaInfo.character)) continue;

                RotateCharacter(textInfo, charaInfo);
            }

            for (int i = 0; i < textInfo.meshInfo.Length; i++)
            {
                textInfo.meshInfo[i].mesh.vertices = textInfo.meshInfo[i].vertices;
                TextMeshPro.UpdateGeometry(textInfo.meshInfo[i].mesh, i);
            }
        }

        static void RotateCharacter(TMP_TextInfo textInfo, TMP_CharacterInfo charaInfo)
        {
            int materialIndex = charaInfo.materialReferenceIndex;
            int vertexIndex = charaInfo.vertexIndex;
            Vector3[] destVertices = textInfo.meshInfo[materialIndex].vertices;

            // 文字の中心を回転の中心にする
            Vector3 rotatedCenterVertex = (destVertices[vertexIndex] + destVertices[vertexIndex + 2]) / 2f;

            Vector3 offset = rotatedCenterVertex;
            destVertices[vertexIndex + 0] += -offset;
            destVertices[vertexIndex + 1] += -offset;
            destVertices[vertexIndex + 2] += -offset;
            destVertices[vertexIndex + 3] += -offset;

            // 回転
            float angle = -90;
            Matrix4x4 matrix = Matrix4x4.TRS(Vector3.zero, Quaternion.Euler(0, 0, angle), Vector3.one);

            destVertices[vertexIndex + 0] = matrix.MultiplyPoint3x4(destVertices[vertexIndex + 0]);
            destVertices[vertexIndex + 1] = matrix.MultiplyPoint3x4(destVertices[vertexIndex + 1]);
            destVertices[vertexIndex + 2] = matrix.MultiplyPoint3x4(destVertices[vertexIndex + 2]);
            destVertices[vertexIndex + 3] = matrix.MultiplyPoint3x4(destVertices[vertexIndex + 3]);

            destVertices[vertexIndex + 0] += offset;
            destVertices[vertexIndex + 1] += offset;
            destVertices[vertexIndex + 2] += offset;
            destVertices[vertexIndex + 3] += offset;
        }
    }
}
