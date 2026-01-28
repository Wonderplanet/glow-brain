using System;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary>
    /// 3x3行列
    /// 2次元ベクトル変換で使う
    /// </summary>
    // ReSharper disable once InconsistentNaming
    public record Matrix3x3(
        float M00, float M01, float M02,
        float M10, float M11, float M12,
        float M20, float M21, float M22)
    {
	    public static Matrix3x3 Identity => new Matrix3x3 (
		    1f, 0f, 0f,
		    0f, 1f, 0f,
		    0f, 0f, 1f);

	    public static Matrix3x3 Translate (float x, float y)
        {
	        return new Matrix3x3 (
		        1f, 0f, x,
		        0f, 1f, y,
		        0f, 0f, 1f);
        }

        public static Matrix3x3 Scale (float x, float y)
        {
	        return new Matrix3x3 (
		        x, 0f, 0f,
		        0f, y, 0f,
		        0f, 0f, 1f);
        }

        public static Matrix3x3 Scale (float scale)
        {
	        return new Matrix3x3 (
		        scale, 0f, 0f,
		        0f, scale, 0f,
		        0f, 0f, 1f);
        }

        public static Matrix3x3 operator *(Matrix3x3 mat1, Matrix3x3 mat2)
		{
			float m00 = mat1.M00 * mat2.M00 + mat1.M10 * mat2.M01 + mat1.M20 * mat2.M02;
			float m10 = mat1.M00 * mat2.M10 + mat1.M10 * mat2.M11 + mat1.M20 * mat2.M12;
			float m20 = mat1.M00 * mat2.M20 + mat1.M10 * mat2.M21 + mat1.M20 * mat2.M22;
			float m01 = mat1.M01 * mat2.M00 + mat1.M11 * mat2.M01 + mat1.M21 * mat2.M02;
			float m11 = mat1.M01 * mat2.M10 + mat1.M11 * mat2.M11 + mat1.M21 * mat2.M12;
			float m21 = mat1.M01 * mat2.M20 + mat1.M11 * mat2.M21 + mat1.M21 * mat2.M22;
			float m02 = mat1.M02 * mat2.M00 + mat1.M12 * mat2.M01 + mat1.M22 * mat2.M02;
			float m12 = mat1.M02 * mat2.M10 + mat1.M12 * mat2.M11 + mat1.M22 * mat2.M12;
			float m22 = mat1.M02 * mat2.M20 + mat1.M12 * mat2.M21 + mat1.M22 * mat2.M22;

			return new Matrix3x3 (m00, m01, m02, m10, m11, m12, m20, m21, m22);
		}

        public static Vector2 operator *(Matrix3x3 matrix, Vector2 vec)
        {
	        float x = matrix.M00 * vec.x + matrix.M01 * vec.y + matrix.M02;
	        float y = matrix.M10 * vec.x + matrix.M11 * vec.y + matrix.M12;

	        return new Vector2(x, y);
        }

        public float GetDeterminant ()
        {
	        return M00 * (M11 * M22 - M12 * M21)
	               - M01 * (M10 * M22 - M12 * M20)
	               + M02 * (M10 * M21 - M11 * M20);
        }

        public Matrix3x3 Inverse ()
		{
			float determinant = GetDeterminant ();
			if (determinant == 0)
			{
				throw new InvalidOperationException("Can't invert matrix with determinant 0");
			}

			float invDeterminant = 1f / determinant;

			float m00 = (M11 * M22 - M21 * M12) * invDeterminant;
			float m10 = (M20 * M12 - M10 * M22) * invDeterminant;
			float m20 = (M10 * M21 - M20 * M11) * invDeterminant;
			float m01 = (M21 * M02 - M01 * M22) * invDeterminant;
			float m11 = (M00 * M22 - M20 * M02) * invDeterminant;
			float m21 = (M20 * M01 - M00 * M21) * invDeterminant;
			float m02 = (M01 * M12 - M11 * M02) * invDeterminant;
			float m12 = (M10 * M02 - M00 * M12) * invDeterminant;
			float m22 = (M00 * M11 - M10 * M01) * invDeterminant;

			return new Matrix3x3 (m00, m01, m02, m10, m11, m12, m20, m21, m22);
		}

        public Vector2 Multiply(float x, float y)
        {
	        float outX = M00 * x + M01 * y + M02;
	        float outY = M10 * x + M11 * y + M12;

	        return new Vector2(outX, outY);
        }
    }
}
