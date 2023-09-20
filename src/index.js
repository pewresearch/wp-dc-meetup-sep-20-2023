/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEntityProp, useResourcePermissions } from '@wordpress/core-data';
import { PanelBody, Spinner, TextControl } from '@wordpress/components';
import { Warning } from '@wordpress/block-editor';
import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar } from '@wordpress/edit-post';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

function ExampleRealtimeSyncField({postId, postType}) {
	const { canDelete, isResolving } = useResourcePermissions('posts', postId);

	const [ meta, setMeta ] = useEntityProp('postType', postType, 'meta', postId);

	if ( isResolving ) {
		return <Spinner/>
	}

	if ( !canDelete ) {
		return <Warning>{__('You do not have permission to edit this object in this context.', 'entity-field-example')}</Warning>
	}

	const value = meta?.example_synced_field;

	return(
		<TextControl label={__('My Magicaly Cross-Syncing Field ✨', 'entity-field-example')} value={value} onChange={(newVal) => {
			setMeta({
				...meta,
				example_synced_field: newVal,
			});
		}} />

	);
}

registerPlugin('entity-field-example', {
	render: () => {
		const { postType, parentId } = useSelect(
			(select) => {
				const post_parent = select(editorStore).getEditedPostAttribute('post_parent');
				const currentPostType = select(editorStore).getCurrentPostType();
				const currentPostId = select(editorStore).getCurrentPostId();
				const currentParentId = 0 !== post_parent ? post_parent : currentPostId;
				return {
					postType: currentPostType,
					postId: currentPostId,
					parentId: currentParentId,
				}
			},
			[]
		);
		return (
			<PluginSidebar name="entity-field-example" title={__('Example Realtime Sync Field', 'entity-field-example')} icon="admin-post">
				<ExampleRealtimeSyncField postId={parentId} postType={postType} />
			</PluginSidebar>
		);
	}
});
