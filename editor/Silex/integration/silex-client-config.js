/* global window, URL, console */

export default async function (config) {
    config.on('silex:startup:end', async () => {
        const editor = config.getEditor();
        const params = new URL(window.location.href).searchParams;
        const pageId = params.get('pageId');
        const pageName = params.get('pageName');

        if (!pageId) {
            return;
        }

        const existingPage = editor.Pages.getAll().find((page) => page.id === pageId);

        if (existingPage) {
            editor.Pages.select(existingPage);
            return;
        }

        const page = editor.Pages.add({
            id: pageId,
            name: pageName,
        });
        const pageById = editor.Pages.get(pageId);

        if (page.id !== pageId || !pageById) {
            console.error(`Silex client config could not create page with id "${pageId}".`, {
                actualPageId: page.id,
                pageId,
            });
            return;
        }

        editor.Pages.select(page);
        await editor.store();
    });

    return {};
}
