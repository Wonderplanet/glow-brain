using Spine;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Modules.Spine.Presentation
{
    public class SpineBoneFollower : MonoBehaviour
    {
		#region Inspector
        [SerializeField] SkeletonRenderer _skeletonRenderer;
		public SkeletonRenderer SkeletonRenderer {
			get { return _skeletonRenderer; }
			set {
				_skeletonRenderer = value;
				Initialize();
			}
		}

        [SerializeField] string _boneName;
        public string BoneName
        {
            get => _boneName;
            set => _boneName = value;
        }

        [SerializeField] bool _followXYPosition = true;
        public bool FollowXYPosition
        {
            get => _followXYPosition;
            set => _followXYPosition = value;
        }

        [SerializeField] bool _followZPosition = true;
        public bool FollowZPosition
        {
            get => _followZPosition;
            set => _followZPosition = value;
        }

        [SerializeField] bool _followBoneRotation = true;
        public bool FollowBoneRotation
        {
            get => _followBoneRotation;
            set => _followBoneRotation = value;
        }

		[Tooltip("Follows the skeleton's flip state by controlling this Transform's local scale.")]
        [SerializeField] bool _followSkeletonFlip = true;
        public bool FollowSkeletonFlip
        {
            get => _followSkeletonFlip;
            set => _followSkeletonFlip = value;
        }

        [Tooltip("Follows the target bone's local scale.")]
        [SerializeField] bool _followLocalScale;
        public bool FollowLocalScale
        {
            get => _followLocalScale;
            set => _followLocalScale = value;
        }

		[Tooltip("Includes the parent bone's lossy world scale. BoneFollower cannot inherit rotated/skewed scale because of UnityEngine.Transform property limitations.")]
        [SerializeField] bool _followParentWorldScale;
        public bool FollowParentWorldScale
        {
            get => _followParentWorldScale;
            set => _followParentWorldScale = value;
        }

        public enum AxisOrientation {
			XAxis = 1,
			YAxis
		}
        [Tooltip("Applies when 'Follow Skeleton Flip' is disabled but 'Follow Bone Rotation' is enabled."
                 + " When flipping the skeleton by scaling its Transform, this follower's rotation is adjusted"
                 + " instead of its scale to follow the bone orientation. When one of the axes is flipped, "
                 + " only one axis can be followed, either the X or the Y axis, which is selected here.")]
        [SerializeField] AxisOrientation _maintainedAxisOrientation = AxisOrientation.XAxis;
        public AxisOrientation MaintainedAxisOrientation
        {
            get => _maintainedAxisOrientation;
            set => _maintainedAxisOrientation = value;
        }

        [SerializeField] bool _initializeOnAwake = true;
        public bool InitializeOnAwake
        {
            get => _initializeOnAwake;
            set => _initializeOnAwake = value;
        }
		#endregion

		public bool Valid { get; set; }
		public Bone Bone { get; set; }

		Transform _skeletonTransform;
		bool _skeletonTransformIsParent;

		/// <summary>
		/// Sets the target bone by its bone name. Returns false if no bone was found. To set the bone by reference, use BoneFollower.bone directly.</summary>
		public bool SetBone (string boneName) {
			Bone = _skeletonRenderer.skeleton.FindBone(boneName);
			if (Bone == null) {
				Debug.LogError("Bone not found: " + boneName, this);
				return false;
			}
			_boneName = boneName;
			return true;
		}

		public void Awake () {
			if (_initializeOnAwake) Initialize();
		}

		public void HandleRebuildRenderer (SkeletonRenderer skeletonRenderer) {
			Initialize();
		}

		public void Initialize () {
			Bone = null;
			Valid = _skeletonRenderer != null && _skeletonRenderer.valid;
			if (!Valid) return;

			_skeletonTransform = _skeletonRenderer.transform;
			_skeletonRenderer.OnRebuild -= HandleRebuildRenderer;
			_skeletonRenderer.OnRebuild += HandleRebuildRenderer;
			_skeletonTransformIsParent = Transform.ReferenceEquals(_skeletonTransform, transform.parent);

			if (!string.IsNullOrEmpty(_boneName))
				Bone = _skeletonRenderer.skeleton.FindBone(_boneName);

#if UNITY_EDITOR
			if (Application.isEditor)
				LateUpdate();
#endif
		}

		void OnDestroy () {
			if (_skeletonRenderer != null)
				_skeletonRenderer.OnRebuild -= HandleRebuildRenderer;
		}

		public void LateUpdate ()
        {
            if (_skeletonRenderer == null) return;
            if (_skeletonTransform == null) return;

			if (!Valid) {
				Initialize();
				return;
			}

#if UNITY_EDITOR
			if (!Application.isPlaying)
				_skeletonTransformIsParent = Transform.ReferenceEquals(_skeletonTransform, transform.parent);
#endif

			if (Bone == null) {
				if (string.IsNullOrEmpty(_boneName)) return;
				Bone = _skeletonRenderer.skeleton.FindBone(_boneName);
				if (!SetBone(_boneName)) return;
			}

			Transform thisTransform = this.transform;
			float additionalFlipScale = 1;
			if (_skeletonTransformIsParent) {
				// Recommended setup: Use local transform properties if Spine GameObject is the immediate parent
				thisTransform.localPosition = new Vector3(_followXYPosition ? Bone.WorldX : thisTransform.localPosition.x,
														_followXYPosition ? Bone.WorldY : thisTransform.localPosition.y,
														_followZPosition ? 0f : thisTransform.localPosition.z);
				if (_followBoneRotation) {
					float halfRotation = Mathf.Atan2(Bone.C, Bone.A) * 0.5f;
					if (_followLocalScale && Bone.ScaleX < 0) // Negate rotation from negative scaleX. Don't use negative determinant. local scaleY doesn't factor into used rotation.
						halfRotation += Mathf.PI * 0.5f;

					Quaternion q = default(Quaternion);
					q.z = Mathf.Sin(halfRotation);
					q.w = Mathf.Cos(halfRotation);
					thisTransform.localRotation = q;
				}
			} else {
				// For special cases: Use transform world properties if transform relationship is complicated
				Vector3 targetWorldPosition = _skeletonTransform.TransformPoint(new Vector3(Bone.WorldX, Bone.WorldY, 0f));
				if (!_followZPosition) targetWorldPosition.z = thisTransform.position.z;
				if (!_followXYPosition) {
                    var position = thisTransform.position;
                    targetWorldPosition.x = position.x;
					targetWorldPosition.y = position.y;
				}

				Vector3 skeletonLossyScale = _skeletonTransform.lossyScale;
				Transform transformParent = thisTransform.parent;
				Vector3 parentLossyScale = transformParent != null ? transformParent.lossyScale : Vector3.one;
				if (_followBoneRotation) {
					float boneWorldRotation = Bone.WorldRotationX;

					if ((skeletonLossyScale.x * skeletonLossyScale.y) < 0)
						boneWorldRotation = -boneWorldRotation;

					if (_followSkeletonFlip || _maintainedAxisOrientation == AxisOrientation.XAxis) {
						if ((skeletonLossyScale.x * parentLossyScale.x < 0))
							boneWorldRotation += 180f;
					} else {
						if ((skeletonLossyScale.y * parentLossyScale.y < 0))
							boneWorldRotation += 180f;
					}

					Vector3 worldRotation = _skeletonTransform.rotation.eulerAngles;
					if (_followLocalScale && Bone.ScaleX < 0) boneWorldRotation += 180f;
					thisTransform.SetPositionAndRotation(targetWorldPosition, Quaternion.Euler(worldRotation.x, worldRotation.y, worldRotation.z + boneWorldRotation));
				} else {
					thisTransform.position = targetWorldPosition;
				}

				additionalFlipScale = Mathf.Sign(skeletonLossyScale.x * parentLossyScale.x
												* skeletonLossyScale.y * parentLossyScale.y);
			}

			Bone parentBone = Bone.Parent;
			if (_followParentWorldScale || _followLocalScale || _followSkeletonFlip) {
				Vector3 localScale = new Vector3(1f, 1f, 1f);
				if (_followParentWorldScale && parentBone != null)
					localScale = new Vector3(parentBone.WorldScaleX, parentBone.WorldScaleY, 1f);
				if (_followLocalScale)
					localScale.Scale(new Vector3(Bone.ScaleX, Bone.ScaleY, 1f));
				if (_followSkeletonFlip)
					localScale.y *= Mathf.Sign(Bone.Skeleton.ScaleX * Bone.Skeleton.ScaleY) * additionalFlipScale;
				thisTransform.localScale = localScale;
			}
		}
    }
}
